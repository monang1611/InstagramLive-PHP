<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExtensionsList
 *
 * @author Arriba-PC
 */
class ExtensionsList extends \ArrayObject {
    public function offsetSet($key, $val) {
        if ($val instanceof Extension) {
            return parent::offsetSet($key, $val);
        }
        throw new \InvalidArgumentException('Value must be a Extension');
    }
}
