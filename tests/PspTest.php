<?php declare(strict_types=1);
/**
 * This file is part of the php-lisp/php-lisp.
 *
 * @Link     https://github.com/php-lisp/php-lisp
 * @Document https://github.com/php-lisp/php-lisp/blob/master/README.md
 * @Contact  itwujunze@gmail.com
 * @License  https://github.com/php-lisp/php-lisp/blob/master/LICENSE
 *
 * (c) Panda <itwujunze@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpLisp\Psp\Tests;

use \BadMethodCallException;
use PhpLisp\Psp\Exceptions\ParsingException;
use PhpLisp\Psp\Psp;
use PhpLisp\Psp\PspList;
use PhpLisp\Psp\Runtime\Arithmetic\Addition;
use PhpLisp\Psp\Runtime\Arithmetic\Subtraction;
use PhpLisp\Psp\Runtime\Define;
use PhpLisp\Psp\Runtime\Lambda;
use PhpLisp\Psp\Runtime\PspFunction;
use PhpLisp\Psp\Scope;

class ProgramTest extends TestCase
{
    public $program;

    public $execResult = null;

    public function setUp()
    {
        $this->program = new Psp('
            (define add +)
            (define sub (lambda [a b] {- a b}))
            (echo (sub (add 5 7) 3))
        ');
    }

    public function testEmptyCode()
    {
        $scope = new Scope();
        $this->assertInstanceOf(Scope::class, $scope);
        $this->program = new Psp('');
        $this->program->execute($scope);
    }

    public function testFromFile()
    {
        $program = Psp::load(__DIR__.'/fixtures/sample.psp');
        $this->assertEquals(3, count($program));
        try {
            Psp::load($f = __DIR__.'/fixtures/exception/sample_error.psp');
            $this->fail();
        } catch (ParsingException $e) {
            $this->assertEquals(file_get_contents($f), $e->code);
            $this->assertEquals($f, $e->getLisphpFile());
            $this->assertEquals(2, $e->getLisphpLine());
            $this->assertEquals(41, $e->getLisphpColumn());
        }
    }

    public function testExecute()
    {
        $scope = new Scope;
        $scope['define'] = new Define();
        $scope['+'] = new Addition();
        $scope['-'] = new Subtraction();
        $scope['lambda'] = new Lambda();
        $scope['echo'] = new PspEcho($this);
        $this->program->execute($scope);
        $this->assertSame($scope['+'], $scope['add']);
        $this->assertSame([9], $this->execResult);
    }

    public function testParse()
    {
        $this->assertSame('define', $this->program[0][0]->symbol);
        $this->assertSame('define', $this->program[1][0]->symbol);
        $this->assertSame('echo', $this->program[2][0]->symbol);
    }

    public function testArrayAccess()
    {
        $this->assertFalse(isset($this->program[-1]));
        $this->assertTrue(isset($this->program[0]));
        $this->assertTrue(isset($this->program[1]));
        $this->assertTrue(isset($this->program[2]));
        $this->assertFalse(isset($this->program[3]));
        $this->assertInstanceOf(PspList::class, $this->program[0]);
        $this->assertInstanceOf(PspList::class, $this->program[1]);
        $this->assertInstanceOf(PspList::class, $this->program[2]);
        $this->expectException(BadMethodCallException::class);
        $this->program[0] = 1;
        unset($this->program[0]);
    }

    public function testIterator()
    {
        $i = 0;
        foreach ($this->program as $j => $form) {
            $this->assertInstanceOf(PspList::class, $form);
            $this->assertSame($i++, $j);
            $forms[] = $form;
        }
        $this->assertSame(3, count($forms));
    }

    public function testCount()
    {
        $this->assertSame(3, count($this->program));
    }
}

final class PspEcho extends PspFunction
{
    public $test;

    public function __construct(ProgramTest $test)
    {
        $this->test = $test;
    }

    public function execute(array $arguments)
    {
        $this->test->execResult = $arguments;
    }
}
