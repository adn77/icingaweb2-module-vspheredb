<?php

namespace Icinga\Module\Vsphere\ManagedObject;

use Icinga\Module\Vsphere\Api;

class VirtualMachine extends ManagedObject
{
    public static function getDefaultPropertySet()
    {
        return array(
            'configStatus',
            'overallStatus',
            'name',
            'parent',
            'guest.hostName',
            'guest.ipAddress',
            'guest.guestState',
            'guest.guestId',
            'guest.guestFullName',
            'guest.guestState',
            'guest.toolsRunningStatus',
            'runtime.bootTime',
            'runtime.host',
            'runtime.powerState',
            'config.annotation',
            'config.hardware.numCPU',
            'config.hardware.memoryMB',
            'config.template',
            'config.version',
            'config.uuid',
        );
    }

    public static function defaultSpecSet(Api $api)
    {
        return array(
            'propSet' => array(
                array(
                    'type' => 'VirtualMachine',
                    'all' => 0,
                    'pathSet' => static::getDefaultPropertySet()
                ),
            ),
            'objectSet' => array(
                'obj' => $api->getServiceInstance()->rootFolder,
                'skip' => false,
                'selectSet' => array(
                    static::getFolderTraversalSpec(),
                    static::getDataCenterVmTraversalSpec(),
                ),
            )
        );
    }
}
