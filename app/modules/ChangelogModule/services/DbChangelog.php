<?php

namespace ChangelogModule;

use \Nette\Utils\Strings;
use Nette\Utils\Finder;

/**
 * Module handles changes in database structure
 * Checks
 *
 * @author Josef Nevoral <josef.nevoral@gmail.com>
 */
class DbChangelog extends \Nette\Object {

	/** @var \Nette\Database\Connection */
	private $connection;

	/** @var \Nette\Database\Table\Selection */
	private $changelogTable;

	/** @string */
	private $changelogPath;

	public function __construct(\Nette\Database\Connection $connection,
		\Nette\Database\Table\Selection $changelogTable,
		$changelogPath = '/changelog/'
	) {
		$this->connection = $connection;
		$this->changelogTable = $changelogTable;
		$this->changelogPath = $changelogPath;
		if (!is_dir($this->changelogPath)) {
			throw new \Nette\DirectoryNotFoundException('Dir "'.$this->changelogPath.'" not found! Create it.');
		}
		if (!is_writeable($this->changelogPath)) {
			throw new \Exception('Dir "'.$this->changelogPath.'" is not writeable.');
		}
	}

	public function executeQueries(\Nette\Database\Table\Selection $queries)
	{
		$errors = array();
		foreach ($queries as $query) {
			try {
				$test = $this->connection->exec($query->query);
				// update query as executed
				$query->update(array('executed' => 1));
			} catch (\Exception $e) {
				// save information about error in query
				$query->update(array('error' => $e->getMessage()));
				$errors[$query->id] = $e->getMessage();
			}
		}
		return $errors;
	}

	public function addNewQueries($description, $queries)
	{
		// create new file and save queries there
		$time = time();
		$filename = $time.'_'.Strings::webalize(Strings::truncate($description, 30)).'.sql';
		file_put_contents($this->changelogPath.$filename, $queries);

		// save queries into database table changelog
		$queries = explode(';', $queries);
		foreach ($queries as $query) {
			$query = trim($query);
			if (empty($query)) {
				continue;
			}
			$data = array(
				'file' => $filename,
				'description' => $description,
				'query' => $query,
				'executed' => 1,
				'ins_timestamp' => $time,
				'ins_dt' => new \DateTime
			);
			$this->changelogTable->insert($data);
		}
		return TRUE;
	}

	/** 
	 *	Checks if in database table changelog are some changes that 
	 *	wasnt executed 
	 */
	public function importNewChangelogData()
	{
		$newChanges = false;
		// check if there are some unexecuted queries in database
		$changelogTable = clone $this->changelogTable;
		if ($changelogTable->where('executed', 0)->count('*') > 0) {
			// there are some unexecuted queries
			return true;
		}

		// load files with database changes
		foreach (Finder::findFiles('*.sql')->in($this->changelogPath) as $key => $file) {
			// check if file was already inserted into changelog table
			$filename = $file->getBasename('.sql');
			$fileParts = explode('_', $filename);
			if (count($fileParts) < 2) {
				throw new \Nette\UnexpectedValueException('Changelog file "'.$filenam.'" has unexpected form. It should be %timestamp%_%name%.sql');
			}
			$changelogTable = clone $this->changelogTable;
			if ($changelogTable->where('file', $file->getBasename())->count('*') > 0) {
				// this file content was already inserted
				continue;
			}
			$newChanges = true;

			// content of the file is not in database table, insert it
			$filePathname = $file->getPathname();
			$fileContent = file_get_contents($filePathname);
			$queries = explode(';', $fileContent);
			foreach ($queries as $query) {
				$query = trim($query);
				if (empty($query)) {
					continue;
				}
				$data = array(
					'file' => $file->getBasename(),
					'description' => substr($fileParts[1], 0),
					'query' => $query,
					'executed' => 0,
					'ins_timestamp' => $fileParts[0],
					'ins_dt' => new \DateTime
				);
				$this->changelogTable->insert($data);
			}
		}
		return $newChanges;
	}

}

?>
