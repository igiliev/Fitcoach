<?php
/**
 * Migration Manager Class
 *
 * This class manages the execution of multiple migrations in sequence
 * through a fluent interface.
 *
 * @since ??
 *
 * @package Divi
 */

namespace ET\Builder\Migration;

/**
 * Migration class for handling sequential migration execution.
 *
 * @since ??
 */
class Migration {

	/**
	 * Stores the migration classes to be executed.
	 *
	 * @since ??
	 *
	 * @var array
	 */
	private $_migrations = [];

	/**
	 * Apply a specific migration and return $this for method chaining.
	 *
	 * @since ??
	 *
	 * @param string $migration_class The migration class name to run.
	 * @return self
	 */
	public function apply( string $migration_class ): self {
		$this->_migrations[] = $migration_class;
		return $this;
	}

	/**
	 * Execute all registered migrations in sequence.
	 *
	 * @since ??
	 *
	 * @return void
	 */
	public function execute(): void {
		foreach ( $this->_migrations as $migration_class ) {
			$migration_class::load();
		}
	}
}

$migration = new Migration();

// Register migrations here.
$migration->apply( FlexboxMigration::class );

$migration->execute();
