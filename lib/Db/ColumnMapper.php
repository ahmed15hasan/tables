<?php

namespace OCA\Tables\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use Psr\Log\LoggerInterface;

/** @template-extends QBMapper<Column> */
class ColumnMapper extends QBMapper {
	protected string $table = 'tables_columns';
	private LoggerInterface $logger;

	public function __construct(IDBConnection $db, LoggerInterface $logger) {
		parent::__construct($db, $this->table, Column::class);
		$this->logger = $logger;
	}

	/**
	 * @param int $id
	 *
	 * @return Column
	 * @throws DoesNotExistException
	 * @throws Exception
	 * @throws MultipleObjectsReturnedException
	 */
	public function find(int $id): Column {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->table)
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		return $this->findEntity($qb);
	}

	/**
	 * @param integer $tableID
	 * @return array
	 * @throws Exception
	 */
	public function findAllByTable(int $tableID): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->table)
			->where($qb->expr()->eq('table_id', $qb->createNamedParameter($tableID)));
		return $this->findEntities($qb);
	}

	/**
	 * @param integer $tableID
	 * @return array
	 * @throws Exception
	 */
	public function findAllIdsByTable(int $tableID): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('id')
			->from($this->table)
			->where($qb->expr()->eq('table_id', $qb->createNamedParameter($tableID)));
		$result = $qb->executeQuery();
		$ids = [];
		while ($row = $result->fetch()) {
			$ids[] = $row['id'];
		}
		return $ids;
	}

	/**
	 * @param array $neededColumnIds
	 * @return array<string> Array with key = columnId and value = [column-type]-[column-subtype]
	 * @throws Exception
	 */

	public function getColumnTypes(array $neededColumnIds): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('id', 'type', 'subtype')
			->from($this->table)
			->where('id IN (:columnIds)')
			->setParameter('columnIds', $neededColumnIds, IQueryBuilder::PARAM_INT_ARRAY);

		// Initialise return array with column types of the meta columns: id, created_by, created_at, last_edit_by, last_edit_at
		$out = [
			-1 => 'number',
			-2 => 'text-line',
			-3 => 'datetime',
			-4 => 'text-line',
			-5 => 'datetime',
		];
		$result = $qb->executeQuery();
		try {
			while ($row = $result->fetch()) {
				$out[$row['id']] = $row['type'].($row['subtype'] ? '-'.$row['subtype']: '');
			}
		} finally {
			$result->closeCursor();
		}
		return $out;
	}


	/**
	 * @param int $tableId
	 * @return int
	 */
	public function countColumns(int $tableId): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->func()->count('*', 'counter'));
		$qb->from($this->table);
		$qb->where(
			$qb->expr()->eq('table_id', $qb->createNamedParameter($tableId))
		);

		try {
			$result = $this->findOneQuery($qb);
			return (int)$result['counter'];
		} catch (DoesNotExistException|MultipleObjectsReturnedException|Exception $e) {
			$this->logger->warning('Exception occurred: '.$e->getMessage().' Returning 0.');
			return 0;
		}
	}
}
