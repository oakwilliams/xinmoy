<?php
/*
 * Group
 *
 * @author Oak Williams <oakwilliams@gmail.com>
 * @date   05/03/2018
 *
 * @copyright 2018 Xinmoy, Inc. All Rights Reserved.
 */


namespace Xinmoy\Swoole;


use Exception;


/**
 * Group
 */
class Group {
    /*
     * Instance
     *
     * @static Group
     */
    protected static $_instance = null;


    /*
     * Members
     *
     * @property array
     */
    protected $_members = null;


    /*
     * Groups
     *
     * @property array
     */
    protected $_groups = null;


    /**
     * Get instance.
     *
     * @return Group
     */
    public static function getInstance() {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    /*
     * Construct.
     */
    protected function __construct() { }


    /**
     * Join.
     *
     * @param int    $fd    fd
     * @param string $group group
     */
    public function join($fd, $group) {
        if (($fd < 0) || empty($group)) {
            throw new Exception('wrong fd/group');
        }

        $this->_members[$group][$fd] = $fd;
        $this->_groups[$fd][$group] = $group;
    }


    /**
     * Leave.
     *
     * @param int    $fd    fd
     * @param string $group group
     */
    public function leave($fd, $group) {
        if (($fd < 0) || empty($group)) {
            throw new Exception('wrong fd/group');
        }

        unset($this->_members[$group][$fd]);
        unset($this->_groups[$fd][$group]);
    }


    /**
     * Leave all.
     *
     * @param int $fd fd
     */
    public function leaveAll($fd) {
        foreach ($this->_leaveAll($fd) as $i) { }
    }


    /*
     * Leave all.
     *
     * @param int $fd fd
     */
    protected function _leaveAll($fd) {
        if ($fd < 0) {
            throw new Exception('wrong fd');
        }

        $groups = $this->getGroups($fd);
        if (empty($groups)) {
            return;
        }

        foreach ($groups as $group) {
            yield $this->leave($fd, $group);
        }
    }


    /**
     * Ungroup.
     *
     * @param string $group group
     */
    public function ungroup($group) {
        foreach ($this->_ungroup($group) as $i) { }
    }


    /*
     * Ungroup.
     *
     * @param string $group group
     */
    protected function _ungroup($group) {
        if (empty($group)) {
            throw new Exception('wrong group');
        }

        $members = $this->getMembers($group);
        if (empty($members)) {
            return;
        }

        foreach ($members as $member) {
            yield $this->leave($member, $group);
        }
    }


    /**
     * Get members.
     *
     * @param string $group group
     *
     * @return array
     */
    public function getMembers($group) {
        if (empty($group)) {
            throw new Exception('wrong group');
        }

        return isset($this->_members[$group]) ? $this->_members[$group] : [];
    }


    /**
     * Get groups.
     *
     * @param int $fd fd
     *
     * @return array
     */
    public function getGroups($fd) {
        if ($fd < 0) {
            throw new Exception('wrong fd');
        }

        return isset($this->_groups[$fd]) ? $this->_groups[$fd] : [];
    }
}
