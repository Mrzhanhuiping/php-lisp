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

namespace PhpLisp\Psp\Exceptions;

class ParsingException extends Exception
{
    public $code;

    public $offset;

    public $pspFile;

    public function __construct($code, $offset, $file = '')
    {
        $this->code = $code;
        $this->offset = $offset;
        $this->pspFile = $file;
        $on = ($file ? "$file:" : '')
            . $this->getPspLine() . ':'
            . $this->getPspColumn();
        $this->message = "parsing error on $on";
    }

    public function getPspFile()
    {
        return $this->pspFile;
    }

    public function getPspLine()
    {
        if ($this->offset <= 0) {
            return 1;
        }

        return substr_count($this->code, "\n", 0, $this->offset) + 1;
    }

    public function getPspColumn()
    {
        $pos = strrpos(substr($this->code, 0, $this->offset), "\n");

        return $this->offset - ($pos === false ? -1 : $pos);
    }
}
