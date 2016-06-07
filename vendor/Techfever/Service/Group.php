<?php
namespace Techfever\Service;

use Techfever\Exception;

class Group extends Services
{

    /**
     * Options
     *
     * @var array
     */
    protected $options = array(
        'group' => 0,
        'service' => 0
    );

    /**
     *
     * @var Bonus
     *
     */
    private $system_group_data = array();

    /**
     * Construct an instance of this class.
     */
    public function __construct($options = null)
    {
        if (! is_array($options)) {
            throw new Exception\RuntimeException('Options has not been set or configured.');
        }
        $options = array_merge($this->options, $options);
        $this->setServiceLocator($options['servicelocator']);
        parent::__construct($options);
        unset($this->options['servicelocator']);
        $this->setOptions($options);
    }

    /**
     * Get Group Data
     *
     * @return array
     */
    public function getGroupData($group = null, $status = null)
    {
        if (is_null($group)) {
            $group = $this->getOption('group');
        }
        if(! is_array($this->system_group_data) || !array_key_exists($group, $this->system_group_data) || count($this->system_group_data[$group]) < 1){
            $QServiceGroup = $this->getDatabase();
            $QServiceGroup->select();
            $QServiceGroup->columns(array(
                'id' => 'system_service_group_id',
                'name' => 'system_service_group_name',
                'status' => 'system_service_group_status',
                'key' => 'system_service_group_key'
            ));
            $QServiceGroup->from(array(
                'ssg' => 'system_service_group'
            ));
            $where = array();
            if (is_int($status) && $status >= 0) {
                $where['ssg.system_service_group_status'] = $status;
            }
            if (is_int($group) && $group > 0) {
                $where['ssg.system_service_group_id'] = $group;
            }
            $QServiceGroup->where($where);
            $QServiceGroup->execute();
            if ($QServiceGroup->hasResult()) {
                while ($QServiceGroup->valid()) {
                    $rawdata = $QServiceGroup->current();
                    if ($rawdata['status'] == 1) {
                        $rawdata['status'] = True;
                    } else {
                        $rawdata['status'] = False;
                    }
                    $rawdata['title'] = $this->getTranslate('text_system_' . $rawdata['key']);
                    $this->system_group_data[$rawdata['id']] = $rawdata;
                    $QServiceGroup->next();
                }
            }
        }
        return $this->system_group_data;
    }

    /**
     * Is Activated Group
     *
     * @return array
     */
    public function isGroupActivated($group = null)
    {
        if (is_null($group)) {
            $group = $this->getOption('group');
        }
        $status = false;
        if (is_int($group) && $group > 0) {
            $data = $this->getGroupData($group, null);
            if (is_array($data) && count($data) > 0) {
                $data = $data[$group];
                if (array_key_exists('status', $data) && $data['status'] === True) {
                    $status = true;
                }
            }
        }
        return $status;
    }

    /**
     * Get Group ID by Key
     *
     * @return array
     */
    public function getGroupIDbyKey($key = null)
    {
        $id = 0;
        if (! is_null($key)) {
            $QServiceGroup = $this->getDatabase();
            $QServiceGroup->select();
            $QServiceGroup->columns(array(
                'id' => 'system_service_group_id'
            ));
            $QServiceGroup->from(array(
                'ssg' => 'system_service_group'
            ));
            $QServiceGroup->where(array(
                'ssg.system_service_group_key' => $key
            ));
            $QServiceGroup->limit(1);
            $QServiceGroup->execute();
            if ($QServiceGroup->hasResult()) {
                while ($QServiceGroup->valid()) {
                    $rawdata = $QServiceGroup->current();
                    $id = $rawdata['id'];
                    $QServiceGroup->next();
                }
            }
        }
        return $id;
    }

    /**
     * Get Service Key by Id
     *
     * @return array
     */
    public function getServiceKeybyID($id = null)
    {
        $key = null;
        if (! is_null($key) && $id > 0) {
            $QServiceGroup = $this->getDatabase();
            $QServiceGroup->select();
            $QServiceGroup->columns(array(
                'key' => 'system_service_group_key'
            ));
            $QServiceGroup->from(array(
                'ssg' => 'system_service_group'
            ));
            $QServiceGroup->where(array(
                'ssg.system_service_group_id' => $id
            ));
            $QServiceGroup->limit(1);
            $QServiceGroup->execute();
            if ($QServiceGroup->hasResult()) {
                while ($QServiceGroup->valid()) {
                    $rawdata = $QServiceGroup->current();
                    $key = $rawdata['key'];
                    $QServiceGroup->next();
                }
            }
        }
        return $key;
    }
}