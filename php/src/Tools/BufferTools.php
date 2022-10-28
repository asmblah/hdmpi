<?php

/**
 * KVM & HDMI capture with TypeScript and PHP using Uniter.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/hdmpi
 *
 * Released under the MIT license
 * https://github.com/asmblah/hdmpi/raw/master/MIT-LICENSE.txt
 */

namespace Asmblah\Hdmpi\Tools;

/**
 * Class BufferTools.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class BufferTools
{
    /**
     * @var object
     */
    private $bufferClass;

    /**
     * @param object $bufferClass
     */
    public function __construct($bufferClass)
    {
        $this->bufferClass = $bufferClass;
    }

    /**
     * @param int $number
     * @return object
     */
    public function numberTo16BitBigEndian($number)
    {
        $buffer = $this->bufferClass->alloc(2);

        $buffer->writeUint16BE($number);

        return $buffer;
    }

    /**
     * @param int $number
     * @return object
     */
    public function numberTo16BitLittleEndian($number)
    {
        $buffer = $this->bufferClass->alloc(2);

        $buffer->writeUint16LE($number);

        return $buffer;
    }

    /**
     * @param int $number
     * @return object
     */
    public function numberTo32BitBigEndian($number)
    {
        $buffer = $this->bufferClass->alloc(4);

        $buffer->writeUint32BE($number);

        return $buffer;
    }
}
