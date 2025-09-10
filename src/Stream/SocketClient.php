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

namespace Fabiang\Xmpp\Stream;

use Fabiang\Xmpp\Exception\ErrorException;
use Fabiang\Xmpp\Exception\InvalidArgumentException;
use Fabiang\Xmpp\Exception\SocketException;
use Fabiang\Xmpp\Util\ErrorHandler;

/**
 * Stream functions wrapper class.
 *
 * @package Xmpp\Stream
 */
class SocketClient
{

    const int BUFFER_LENGTH = 4096;

    /**
     * @var ?resource
     */
    protected $resource = null;
    protected ?string $address = null;

    /**
     * Options used to create a stream context
     * @see http://php.net/manual/en/function.stream-context-create.php
     */
    protected array $options = [];

    public function __construct(string $address, array $options = null)
    {
        $this->address = $address;
        $this->options = $options ?? [];
    }

    /**
     * @param int $timeout Timeout for connection
     * @param bool $persistent Create a persistent connection (use STREAM_CLIENT_PERSISTENT) - Should client socket remain persistent between page loads
     * @throws ErrorException
     */
    public function connect(int $timeout = 30, bool $persistent = false): void
    {
        $flags = STREAM_CLIENT_CONNECT;

        if (true === $persistent) {
            $flags |= STREAM_CLIENT_PERSISTENT;
        }

        // call stream_socket_client with custom error handler enabled
        $handler = new ErrorHandler(
            function ($address, $timeout, $flags, ?array $options = null) {
                $errno  = null;
                $errstr = null;

                if (!empty($options)) {
                    $context = stream_context_create($options);
                    return stream_socket_client($address, $errno, $errstr, $timeout, $flags, $context);
                }

                return stream_socket_client($address, $errno, $errstr, $timeout, $flags);
            },
            $this->address ?? '',
            $timeout,
            $flags,
            $this->options
        );

        $resource = $handler->execute(__FILE__, __LINE__);
        stream_set_timeout($resource, $timeout);
        $this->resource = $resource;
    }

    /**
     * Reconnect and optionally use different address
     */
    public function reconnect(?string $address = null, ?int $timeout = 30, ?bool $persistent = false): void
    {
        $this->close();

        if ($address !== null) {
            $this->address = $address;
        }

        $this->connect($timeout, $persistent);
    }

    public function close(): void
    {
        fclose($this->resource);
    }

    /**
     * Set stream blocking mode
     * @return $this
     */
    public function setBlocking(bool $flag = true): self
    {
        stream_set_blocking($this->resource, $flag);
        return $this;
    }

    /**
     * @param int $length Count of bytes to read
     * @throws SocketException
     */
    public function read(int $length = self::BUFFER_LENGTH): string
    {
        try {
            $handler = new ErrorHandler(fread(...), $this->resource, $length);
            $data    = $handler->execute(__FILE__, __LINE__);

            if ($data === false) {
                throw new SocketException('Socket read failure');
            }

            return $data;
        } catch (ErrorException $e) {
            throw new SocketException('Socket write failure', __LINE__, $e);
        }
    }

    /**
     * @param int $length Limit of bytes to write
     * @throws SocketException
     */
    public function write(string $string, int $length = null): int
    {
        try {
            $handler = new ErrorHandler(fwrite(...), $this->resource, $string, $length);
            $result  = $handler->execute(__FILE__, __LINE__);

            if ($result === false) {
                throw new SocketException('Socket write failure');
            }

            return $result;
        } catch (ErrorException $e) {
            throw new SocketException('Socket write failure', __LINE__, $e);
        }
    }

    /**
     * Enable/disable cryptography on stream
     * @param int $cryptoType One of the STREAM_CRYPTO_METHOD_* constants
     * @throws ErrorException
     * @throws InvalidArgumentException
     */
    public function crypto(bool $enable, ?int $cryptoType = null): int|bool
    {
        if (false === $enable) {
            $handler = new ErrorHandler(stream_socket_enable_crypto(...), $this->resource, false);
            return $handler->execute(__FILE__, __LINE__);
        }

        if (null === $cryptoType) {
            throw new InvalidArgumentException('Argument #2 $cryptoType of '.__CLASS__.'::'.__METHOD__.'() is required when enabling encryption on a stream');
        }

        return stream_socket_enable_crypto($this->resource, $enable, $cryptoType);
    }

    /**
     * @return resource|null
     */
    public function getResource()
    {
        return $this->resource;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }
}
