<?php
namespace Codeception\Module;

class DbzHelper extends \Codeception\Module implements \Codeception\Util\DbInterface
{
    public function getDbAdapter()
    {
        if ($this->hasModule('ZF1')) {
            return $this->getModule('ZF1')->db;
        } else {
            return \Zend_Registry::get('Zend_Db');
        }
    }

    public function _before(\Codeception\TestCase $test)
    {
        $this->getDbAdapter()->beginTransaction();
    }

    public function _after(\Codeception\TestCase $test)
    {
        try {
            $this->getDbAdapter()->rollback();
        } catch (\Exception $e) {
            if ($e->getMessage() == \Magento\DB\Adapter\AdapterInterface::ERROR_ASYMMETRIC_ROLLBACK_MESSAGE) {
                // Catch exception that means that the DB query failed!
                // This usually happens because something else failed in the query.
                // eg. "Integrity constraint violation"
            } else {
                throw $e;
            }
        }
    }

    public function haveInDatabase($table, array $data)
    {
        $query = $this->getModule('Db')->driver->insert($table, $data);
        $this->debugSection('Query', $query);

        $sth = $this->getDbAdapter()->insert($table, $data);
        if (!$sth) {
            $this->fail(sprintf(
                "Record with %s couldn't be inserted into %s",
                json_encode($data), $table)
            );
        }
        $this->insertedIds[] = array(
            'table' => $table,
            'id' => $this->getDbAdapter()->lastInsertId()
        );
    }

    public function seeInDatabase($table, $criteria = array())
    {
        $res = $this->proceedSeeInDatabase($table, "count(*)", $criteria);
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $res);
    }

    public function seeInDatabaseAtLeast($count, $table, $criteria = array())
    {
        $res = $this->proceedSeeInDatabase($table, "count(*)", $criteria);
        \PHPUnit_Framework_Assert::assertGreaterThanOrEqual($count, $res);
    }

    public function seeInDatabaseExactly($count, $table, $criteria = array())
    {
        $res = $this->proceedSeeInDatabase($table, "count(*)", $criteria);
        \PHPUnit_Framework_Assert::assertEquals($count, $res);
    }

    public function dontSeeInDatabase($table, $criteria = array())
    {
        $res = $this->proceedSeeInDatabase($table, "count(*)", $criteria);
        \PHPUnit_Framework_Assert::assertLessThan(1, $res);
    }

    protected function proceedSeeInDatabase($table, $column, $criteria, $row = 0)
    {
        if ($criteria) {
            $query = "select %s from %s where %s";

            $params = array();
            foreach ($criteria as $k => $v) {
                $params[] = "`$k` = ?";
            }
            $params = implode('AND ',$params);

            $query = sprintf($query, $column, $table, $params);

            $this->debugSection('Query',$query, $params);
            $column = $this->getDbAdapter()->fetchCol($query, array_values($criteria));
            return $column[$row];
        } else {
            $query = "select %s from %s";
            $query = sprintf($query, $column, $table);

            $this->debugSection('Query',$query, []);
            $column = $this->getDbAdapter()->fetchCol($query);
            return $column[$row];
        }
    }

    public function grabFromDatabase($table, $column, $criteria = array(), $row = 0)
    {
        return $this->proceedSeeInDatabase($table, $column, $criteria, $row);
    }
}
