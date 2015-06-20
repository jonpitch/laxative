<?php

namespace Codeception\Extension;

/**
 *
 * Fiber, for your dump.
 * Easy to use, saves you time.
 * DevOps for the win.
 *  - Benjamin Franklin (probably)
 *
 */
class Laxative extends \Codeception\Platform\Extension
{
	// listen to these events
	public static $events = array(
		'suite.before' => 'beforeSuite',
		'suite.after' => 'afterSuite',
	);

	private $_backup;
	private $_backupPath;
  private $_migrations;
  private $_seed;
	private $_host;
	private $_login;
	private $_database;

	// the Codeception Db module
	private $_db;

	public function beforeSuite(\Codeception\Event\SuiteEvent $e)
	{
		// read config
		$this->_backup = $this->config['backup'];
		$this->_backupPath = $this->config['backup_path'];
    $this->_migrations = $this->config['migrations'];
    $this->_seed = $this->config['seed'];
		$this->_host = $this->config['host'];
		$this->_login = $this->config['login'];
		$this->_database = $this->config['database'];

		// get the db module
    try {
      $this->_db = $this->getModule('Db');
    } catch (\Exception $e) {
      return;
    }

		// back up local database if enabled
		if ($this->_backup) {
			$this->backup();
		}

		// start from scratch
		$this->localRestore();

		// run migrations
		$this->migrate();

		// run seeder(s)
		$this->seed();

		// create Codeception dump
		$this->dump();

		// update Codeception to populate the database
		$this->updateDbModule();
	}

	public function afterSuite(\Codeception\Event\SuiteEvent $e)
	{
		// read config
		$this->_backup = $this->config['backup'];
		$this->_backupPath = $this->config['backup_path'];

		if ($this->_backup) {
			$this->restore();
		}
	}

	/**
	 * Create a binary back up of the local database.
	 */
	private function backup()
	{
		$this->writeln('Laxative: Backing up your local database to: ' . $this->_backupPath . '...');

		// use pg_dump to create binary backup
		$command = sprintf('pg_dump -h %s -U %s -d %s -F t --file %s',
			$this->_host,
			$this->_login,
			$this->_database,
			$this->_backupPath);

		exec($command);

		$this->writeln('Done.');


	}

	/**
	 * Let Codeception restore the database from base.sql
	 */
	private function localRestore()
	{
		$this->writeln('Laxative: Restoring local database from base...');

		$this->_db->_reconfigure(array('populate' => true));
		$this->_db->_initialize();

		$this->writeln('Done.');
	}

	/**
	 * Restore local database from a binary backup.
	 * @see backup()
	 */
	private function restore()
	{
		// get the db module - not needed but will prevent restore for
		// suites that don't use Db
	    try {
	      $this->_db = $this->getModule('Db');
	    } catch (\Exception $e) {
	      return;
	    }

		$this->writeln('Laxative: Restoring your database from backup...');

		// use pg_restore to restore from our binary backup
		$command = sprintf('pg_restore -h %s -U %s -d %s -c %s',
			$this->_host,
			'postgres',
			$this->_database,
			$this->_backupPath);

		exec($command);

		$this->writeln('Done.');
	}

	/**
	 * Run all migrations against fresh database.
	 */
	private function migrate()
	{
    if (!empty($this->_migrations)) {
      $this->writeln('Laxative: Running migrations...');
      exec($this->_migrations);
      $this->writeln('Done.');
    }
	}

	/**
	 * Run all seeders against newly created database.
	 */
	private function seed()
	{
    if (!empty($this->_seed)) {
      $this->writeln('Laxative: Seeding database...');
      exec($this->_seed);
      $this->writeln('Done.');
    }
	}

	/**
	 * Create a dump file for the Codeception Db module.
	 */
	private function dump()
	{
		$this->writeln('Laxative: Creating Codeception dump...');

		$command = sprintf('pg_dump -h %s -U %s -d %s > tests/_data/dump.sql',
			$this->_host,
			$this->_login,
			$this->_database);

		exec($command);

		$this->writeln('Done.');
	}

	/**
	 * Re-configure the Db module to ensure we populate from the dump file.
	 */
	private function updateDbModule()
	{
		$this->writeln('Laxative: Re-configuring Codeception Db module...');

		$this->_db->_reconfigure(array('dump' => 'tests/_data/dump.sql'));
		$this->_db->_initialize();

		$this->writeln('Done.');
	}
}
