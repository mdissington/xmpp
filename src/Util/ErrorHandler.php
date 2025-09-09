<?php

/**
 * Copyright 2014 Fabian Grutschus. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies,
 * either expressed or implied, of the copyright holders.
 *
 * @author    Fabian Grutschus <f.grutschus@lubyte.de>
 * @copyright 2014 Fabian Grutschus. All rights reserved.
 * @license   BSD
 * @link      http://github.com/fabiang/xmpp
 */

namespace Fabiang\Xmpp\Util;

use Fabiang\Xmpp\Exception\ErrorException;
use Fabiang\Xmpp\Exception\InvalidArgumentException;

/**
 * XML utility methods.
 *
 * @package Xmpp\Util
 */
class ErrorHandler
{

    /**
     * Method to be called.
     * @var callable
     */
    protected $method;

    /**
     * Arguments for method.
     */
    protected array $arguments = [];

    /**
     * @param string ...$arguments Optional arguments to be passed to the function call
     */
    public function __construct(callable $method, mixed ...$arguments)
    {
        $this->method    = $method;
        $this->arguments = $arguments;
    }

    /**
     * Execute a function and handle all types of errors.
     * @throws ErrorException
     */
    public function execute(string $file, int $line): mixed
    {
        set_error_handler(function ($errno, $errstr) use ($file, $line) {
            throw new ErrorException($errstr, 0, $errno, $file, $line);
        });

        try {
            $value = ($this->method)(...$this->arguments);

            return $value;
        } finally {
            restore_error_handler();
        }
    }
}
