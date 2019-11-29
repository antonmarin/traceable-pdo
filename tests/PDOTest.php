<?php

namespace antonmarin\TraceablePDOTests;

use antonmarin\TraceablePDO\PDO;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * @coversDefaultClass PDO
 */
class PDOTest extends PHPUnit_Framework_TestCase
{
    /**
     * @coversNothing
     */
    public function testAutoload()
    {
        $this->assertTrue(class_exists(PDO::class));
    }

    /**
     * @covers ::prepare
     */
    public function testPrepareShouldAddComment()
    {
        $pdo = $this->getMockBuilder(PDO::class)
                    ->setConstructorArgs(array('sqlite::memory:'))
                    ->setMethods(array('getTrace', 'cutTrace', 'formatTrace', 'comment'))
                    ->getMock();
        $pdo->method('getTrace')
            ->willReturn(array());
        $pdo->method('cutTrace')
            ->willReturn(array());
        $pdo->method('formatTrace')
            ->willReturn('');
        $pdo->method('comment')
            ->willReturn(' /* some comment */ ');

        /** @var PDO $pdo */
        $statement = $pdo->prepare('SELECT 1');
        $this->assertEquals('SELECT 1 /* some comment */ ', $statement->queryString);
    }

    /**
     * @depends testPrepareShouldAddComment
     * @covers ::comment
     * @covers ::encode
     */
    public function testPrepareShouldCommentFormattedTrace()
    {
        $pdo = $this->getMockBuilder(PDO::class)
                    ->setConstructorArgs(array('sqlite::memory:'))
                    ->setMethods(array('getTrace', 'cutTrace', 'formatTrace'))
                    ->getMock();
        $pdo->method('getTrace')
            ->willReturn(array());
        $pdo->method('cutTrace')
            ->willReturn(array());
        $formattedTrace = ' /* some trace string */'; // with comment to disable inflect on statement
        $pdo->method('formatTrace')
            ->willReturn($formattedTrace);

        $encodedString = base64_encode(gzcompress($formattedTrace));
        /** @var PDO $pdo */
        $statement = $pdo->prepare('SELECT 1');
        $this->assertEquals("SELECT 1 /* {$encodedString} */ ", $statement->queryString);
    }

    /**
     * @depends testPrepareShouldAddComment
     * @covers ::formatTrace
     */
    public function testPrepareShouldFormatCuttedTrace()
    {
        $pdo = $this->getMockBuilder(PDO::class)
                    ->setConstructorArgs(array('sqlite::memory:'))
                    ->setMethods(array('getTrace', 'cutTrace', 'comment'))
                    ->getMock();
        $pdo->method('getTrace')
            ->willReturn(array());
        $cuttedTrace = array(
            array('file' => 'file1', 'line' => 10),
        );
        $pdo->method('cutTrace')
            ->willReturn($cuttedTrace);
        $pdo->method('comment')
            ->willReturnCallback(static function ($formattedTrace) {
                return ' /* ' . $formattedTrace . ' */';
            });

        $formattedTrace = sprintf('#%d %s:%d', 0, $cuttedTrace[0]['file'], $cuttedTrace[0]['line']);
        /** @var PDO $pdo */
        $statement = $pdo->prepare('SELECT 1');
        $this->assertEquals("SELECT 1 /* {$formattedTrace} */", $statement->queryString);
    }

    /**
     * @depends testPrepareShouldAddComment
     * @covers ::cutTrace
     */
    public function testPrepareShouldCutTraceIfTraceLevelSet()
    {
        $pdo = $this->getMockBuilder(PDO::class)
                    ->setConstructorArgs(array('sqlite::memory:'))
                    ->setMethods(array('getTrace', 'formatTrace', 'comment'))
                    ->getMock();
        $trace = array(
            array('file' => 'file1', 'line' => 10),
            array('file' => 'file2', 'line' => 12),
        );
        $traceLevel = 1;
        $pdo->method('getTrace')
            ->willReturn($trace);
        $pdo->method('formatTrace')
            ->willReturnCallback(static function ($cutTrace) {
                $result = '';
                foreach ($cutTrace as $item) {
                    $result .= $item['file'] . ':' . $item['line'];
                }

                return $result;
            });
        $pdo->method('comment')
            ->willReturnCallback(static function ($formattedTrace) {
                return ' /* ' . $formattedTrace . ' */';
            });


        /** @var PDO $pdo */
        $statement = $pdo->prepare('SELECT 1');
        $this->assertEquals(
            'SELECT 1 ' .
            '/* ' . $trace[0]['file'] . ':' . $trace[0]['line'] . $trace[1]['file'] . ':' . $trace[1]['line'] . ' */',
            $statement->queryString
        );

        $cutTrace = array_slice($trace, 0, $traceLevel);
        $pdo->traceLevel = $traceLevel;
        $statement = $pdo->prepare('SELECT 1');
        $this->assertEquals(
            'SELECT 1 /* ' . $cutTrace[0]['file'] . ':' . $cutTrace[0]['line'] . ' */',
            $statement->queryString
        );
    }

    /**
     * @depends testPrepareShouldAddComment
     * @covers ::getTrace
     */
    public function testPrepareShouldAddTraceWithoutInternalRoutes()
    {
        $pdo = $this->getMockBuilder(PDO::class)
                    ->setConstructorArgs(array('sqlite::memory:'))
                    ->setMethods(array('cutTrace', 'formatTrace', 'comment'))
                    ->getMock();
        $pdo->method('cutTrace')
            ->willReturnArgument(0);
        $pdo->method('formatTrace')
            ->willReturnCallback(static function ($cutTrace) {
                $result = '';
                foreach ($cutTrace as $item) {
                    if (!isset($item['file'])) {
                        continue;
                    }
                    $result .= $item['file'] . ':' . $item['line'];
                }

                return $result;
            });
        $pdo->method('comment')
            ->willReturnCallback(static function ($formattedTrace) {
                return " /* {$formattedTrace} */";
            });


        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_unshift($trace, array('file' => __FILE__, 'line' => 193)); // line of ->prepare() call
        $reflector = new ReflectionClass(PDO::class);
        $pdoFile = $reflector->getFileName();
        $trace = array_filter($trace, static function ($row) use ($pdoFile){
            return !isset($row['file']) || $row['file'] !== $pdoFile;
        });
        $result = '';
        foreach ($trace as $item) {
            if (!isset($item['file'])) {
                continue;
            }
            $result .= $item['file'] . ':' . $item['line'];
        }

        /** @var PDO $pdo */
        $statement = $pdo->prepare('SELECT 1');
        $this->assertEquals(
            "SELECT 1 /* {$result} */",
            $statement->queryString
        );
    }
}
