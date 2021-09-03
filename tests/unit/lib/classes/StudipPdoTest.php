<?php

/*
 * studip_pdo_test.php - unit tests for the StudipPDO class
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class StudipPdoTest extends \Codeception\Test\Unit
{
    public function setUp(): void
    {
        $dsn =
            'mysql:host=' .
            $GLOBALS['DB_STUDIP_HOST'] .
            ';dbname=' .
            $GLOBALS['DB_STUDIP_DATABASE'] .
            ';charset=utf8mb4';
        $username = $GLOBALS['DB_STUDIP_USER'];
        $password = $GLOBALS['DB_STUDIP_PASSWORD'];

        $this->testPdo = new class ($dsn, $username, $password) extends StudipPDO {
            public static function doReplaceStrings($statement)
            {
                return parent::replaceStrings($statement);
            }
        };

        $this->testPdo->beginTransaction();
    }

    public function tearDown(): void
    {
        $this->testPdo->rollBack();
    }

    public function testSimpleString()
    {
        $query = 'SELECT * FROM bar';
        $expect = $query;

        $this->assertEquals($expect, $this->testPdo::doReplaceStrings($query));
    }

    public function testDoubleQuotedString()
    {
        $query = 'SELECT "\'foo""\\"" FROM bar WHERE foo = "\\\\"';
        $expect = 'SELECT ? FROM bar WHERE foo = ?';

        $this->assertEquals($expect, $this->testPdo::doReplaceStrings($query));

        $query = str_repeat($query, 100);
        $expect = str_repeat($expect, 100);

        $this->assertEquals($expect, $this->testPdo::doReplaceStrings($query));
    }

    public function testSingleQuotedString()
    {
        $query = 'SELECT \'"foo\'\'\\\'\' FROM bar WHERE foo = \'\\\\\'';
        $expect = 'SELECT ? FROM bar WHERE foo = ?';

        $this->assertEquals($expect, $this->testPdo::doReplaceStrings($query));

        $query = str_repeat($query, 100);
        $expect = str_repeat($expect, 100);

        $this->assertEquals($expect, $this->testPdo::doReplaceStrings($query));
    }

    public function testMixedQuotedString()
    {
        $query = 'SELECT """\'", \'"\' FROM bar WHERE foo IN (\'\'\'"\'"")';
        $expect = 'SELECT ?, ? FROM bar WHERE foo IN (??)';

        $this->assertEquals($expect, $this->testPdo::doReplaceStrings($query));

        $query = str_repeat($query, 100);
        $expect = str_repeat($expect, 100);

        $this->assertEquals($expect, $this->testPdo::doReplaceStrings($query));
    }

    public function testUnterminatedSingleQuote()
    {
        $query = 'SELECT \'1\' ORDER BY \'au.username asc';
        $expect = 'SELECT ? ORDER BY \'au.username asc';

        $this->assertEquals($expect, $this->testPdo::doReplaceStrings($query));
    }

    public function testUnterminatedDoubleQuote()
    {
        $query = 'SELECT "1" ORDER BY "au.username asc';
        $expect = 'SELECT ? ORDER BY "au.username asc';

        $this->assertEquals($expect, $this->testPdo::doReplaceStrings($query));
    }
}
