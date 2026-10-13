<?php

declare(strict_types=1);

namespace MunicipioQualityExtensions\Tests\Performance;

use MunicipioQualityExtensions\Performance\NavigationIndex;
use MunicipioQualityExtensions\Tests\Support\OptionsState;
use MunicipioQualityExtensions\Tests\Support\WordPressState;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use wpdb;

require_once dirname(__DIR__) . '/Support/wpdb.php';
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

/**
 * Keep independent failure and recovery scenarios together with the shared fixture.
 * @mago-expect lint:too-many-methods
 */
final class NavigationIndexTest extends TestCase
{
    private wpdb $db;
    private NavigationIndex $migration;

    protected function setUp(): void
    {
        WordPressState::reset();
        $this->db = new wpdb();
        $this->migration = new NavigationIndex($this->db);
    }

    public function testExplicitOptInIdempotenceAndRollback(): void
    {
        static::assertFalse($this->migration->status()['enabled']);
        static::assertSame([], $this->db->ddl);
        static::assertTrue($this->migration->enable()['migration']['owned']);
        $this->migration->enable();
        static::assertCount(1, $this->db->ddl);
        static::assertFalse($this->db->locked);
        static::assertFalse($this->migration->disable()['enabled']);
        static::assertCount(2, $this->db->ddl);
    }

    public function testExternalEquivalentIndexIsNeverRemoved(): void
    {
        $this->db->seed('external_navigation');
        static::assertFalse($this->migration->enable()['migration']['owned']);
        $this->migration->disable();
        static::assertSame([], $this->db->ddl);
        static::assertCount(3, $this->db->rows);
    }

    public function testFailedDdlKeepsPendingStateAndCanRetry(): void
    {
        $this->db->failDdl = true;
        try {
            $this->migration->enable();
            static::fail('DDL should fail');
        } catch (RuntimeException) {
            static::assertFalse($this->db->locked);
            static::assertSame(0, OptionsState::$values[NavigationIndex::OPTION]['version']);
        }
        $this->db->failDdl = false;
        static::assertTrue($this->migration->enable()['enabled']);
    }

    public function testInterruptedCreationDoesNotAssumeOwnership(): void
    {
        OptionsState::$values[NavigationIndex::OPTION] = [
            'version' => 0,
            'pending' => true,
            'index' => NavigationIndex::NAME,
            'owned' => false,
        ];
        $this->db->seed(NavigationIndex::NAME);
        static::assertFalse($this->migration->enable()['migration']['owned']);
        $this->migration->disable();
        static::assertSame([], $this->db->ddl);
    }

    public function testChangedOwnedIndexCannotBeRemoved(): void
    {
        $this->migration->enable();
        $this->db->rows[0]['Sub_part'] = 16;
        try {
            $this->migration->disable();
            static::fail('Changed index must be preserved');
        } catch (RuntimeException) {
            static::assertCount(1, $this->db->ddl);
            static::assertFalse($this->db->locked);
        }
    }

    public function testEquivalentExternalIndexDoesNotLoseExistingOwnership(): void
    {
        $this->db->seed('external_navigation');
        $this->db->seed(NavigationIndex::NAME);
        OptionsState::$values[NavigationIndex::OPTION] = [
            'version' => NavigationIndex::VERSION,
            'index' => NavigationIndex::NAME,
            'owned' => true,
        ];
        static::assertTrue($this->migration->enable()['migration']['owned']);
        static::assertSame(['external_navigation'], $this->migration->disable()['matching_indexes']);
    }

    public function testIgnoredReservedIndexIsNotAdoptedOrOverwritten(): void
    {
        $this->db->seed(NavigationIndex::NAME);
        $this->db->rows[0]['Ignored'] = 'YES';
        try {
            $this->migration->enable();
            static::fail('An ignored index must not satisfy opt-in');
        } catch (RuntimeException) {
            static::assertSame([], $this->db->ddl);
            static::assertSame([], OptionsState::$values);
        }
    }

    public function testBusyLockPreventsWrites(): void
    {
        $this->db->busy = true;
        try {
            $this->migration->enable();
            static::fail('Concurrent migration must be rejected');
        } catch (RuntimeException) {
            static::assertSame([], $this->db->ddl);
            static::assertSame([], OptionsState::$values);
        }
    }

    public function testOptionFailurePreventsUntrackedDdl(): void
    {
        OptionsState::$writesFail = true;
        try {
            $this->migration->enable();
            static::fail('State write must succeed before DDL');
        } catch (RuntimeException) {
            static::assertSame([], $this->db->ddl);
            static::assertFalse($this->db->locked);
        }
    }

    public function testNewerMigrationVersionPreventsDowngrade(): void
    {
        OptionsState::$values[NavigationIndex::OPTION] = ['version' => 2];
        $this->expectException(RuntimeException::class);
        $this->migration->enable();
    }

    public function testCurrentBlogTableIsUsed(): void
    {
        $this->db->postmeta = 'custom_42_postmeta';
        $this->migration->enable();
        static::assertStringContainsString('`custom_42_postmeta`', $this->db->ddl[0]);
    }
}
