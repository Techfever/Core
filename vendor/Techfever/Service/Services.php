<?php
namespace Techfever\Service;

use Techfever\Exception;
use Techfever\Functions\General as GeneralBase;

class Services extends GeneralBase
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
    private $system_service_data = array();

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
     * Get Service Data
     *
     * @return array
     */
    public function getServiceData($service = null, $group = null, $status = 1)
    {
        if (is_null($group)) {
            $group = $this->getOption('group');
        }
        if (is_null($service)) {
            $service = $this->getOption('service');
        }
        if (! array_key_exists($group, $this->system_service_data)) {
            $this->system_service_data[$group] = array();
        }
        if (! is_array($this->system_service_data[$group]) || ! array_key_exists($service, $this->system_service_data[$group]) || count($this->system_service_data[$group][$service]) < 1) {
            $QService = $this->getDatabase();
            $QService->select();
            $QService->columns(array(
                'id' => 'system_service_id',
                'group_id' => 'system_service_group_id',
                'name' => 'system_service_name',
                'key' => 'system_service_key',
                'status' => 'system_service_status',
                'class' => 'system_service_class',
                'class_alias' => 'system_service_alias',
                'priority' => 'system_service_priority'
            ));
            $QService->from(array(
                'ss' => 'system_service'
            ));
            $where = array(
                'ss.system_service_status' => $status
            );
            if (is_int($group) && $group > 0) {
                $where['ss.system_service_group_id'] = $group;
            }
            if (is_int($service) && $service > 0) {
                $where['ss.system_service_id'] = $service;
            }
            $QService->where($where);
            $QService->order(array(
                'ss.system_service_priority'
            ));
            $QService->execute();
            if ($QService->hasResult()) {
                while ($QService->valid()) {
                    $rawdata = $QService->current();
                    if ($rawdata['status'] == 1) {
                        $rawdata['status'] = True;
                    } else {
                        $rawdata['status'] = False;
                    }
                    $rawdata['title'] = $this->getTranslate('text_system_' . $rawdata['key']);
                    $this->system_service_data[$group][$rawdata['id']] = $rawdata;
                    $QService->next();
                }
            }
        }
        return $this->system_service_data[$group];
    }

    /**
     * Is Activated Group
     *
     * @return array
     */
    public function isServiceActivated($service = null, $group = null)
    {
        if (is_null($group)) {
            $group = $this->getOption('group');
        }
        if (is_null($service)) {
            $service = $this->getOption('service');
        }
        $status = false;
        if ((is_int($group) && $group > 0) && (is_int($service) && $service > 0)) {
            $data = $this->getServiceData($service, $group, null);
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
     * Get Service ID by Key
     *
     * @return array
     */
    public function getServiceIDbyKey($key = null)
    {
        $id = 0;
        if (! is_null($key)) {
            $QService = $this->getDatabase();
            $QService->select();
            $QService->columns(array(
                'id' => 'system_service_id'
            ));
            $QService->from(array(
                'ss' => 'system_service'
            ));
            $QService->where(array(
                'ss.system_service_key' => $key
            ));
            $QService->limit(1);
            $QService->execute();
            if ($QService->hasResult()) {
                while ($QService->valid()) {
                    $rawdata = $QService->current();
                    $id = $rawdata['id'];
                    $QService->next();
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
            $QService = $this->getDatabase();
            $QService->select();
            $QService->columns(array(
                'key' => 'system_service_key'
            ));
            $QService->from(array(
                'ss' => 'system_service'
            ));
            $QService->where(array(
                'ss.system_service_id' => $id
            ));
            $QService->limit(1);
            $QService->execute();
            if ($QService->hasResult()) {
                while ($QService->valid()) {
                    $rawdata = $QService->current();
                    $key = $rawdata['key'];
                    $QService->next();
                }
            }
        }
        return $key;
    }
}