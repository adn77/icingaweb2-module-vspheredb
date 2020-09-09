<?php

namespace Icinga\Module\Vspheredb\Web\Table\Object;

use gipfl\IcingaWeb2\Icon;
use gipfl\IcingaWeb2\Link;
use gipfl\Translation\TranslationHelper;
use gipfl\IcingaWeb2\Widget\NameValueTable;
use Icinga\Exception\NotFoundError;
use Icinga\Module\Vspheredb\DbObject\VCenter;
use Icinga\Module\Vspheredb\DbObject\VirtualMachine;
use Icinga\Module\Vspheredb\PathLookup;
use ipl\Html\Html;

class VmExtraInfoTable extends NameValueTable
{
    use TranslationHelper;

    /** @var VirtualMachine */
    protected $vm;

    /** @var VCenter */
    protected $vCenter;

    public function __construct(VirtualMachine $vm)
    {
        $this->vm = $vm;
        $this->vCenter = VCenter::load($vm->get('vcenter_uuid'), $vm->getConnection());
    }

    protected function getDb()
    {
        return $this->vm->getConnection();
    }

    /**
     * @throws \Icinga\Exception\NotFoundError
     */
    protected function assemble()
    {
        $vm = $this->vm;
        $uuid = $vm->get('uuid');
        /** @var \Icinga\Module\Vspheredb\Db $connection */
        $connection = $vm->getConnection();
        $lookup =  new PathLookup($connection);
        $path = Html::tag('span', ['class' => 'dc-path'])->setSeparator(' > ');
        foreach ($lookup->getObjectNames($lookup->listPathTo($uuid, false)) as $parentUuid => $name) {
            $path->add(Link::create(
                $name,
                'vspheredb/vms',
                ['uuid' => bin2hex($parentUuid)],
                ['data-base-target' => '_main']
            ));
        }

        $this->addNameValuePairs([
            $this->translate('UUID') => Html::tag('pre', $vm->get('bios_uuid')),
            $this->translate('Instance UUID') => Html::tag('pre', $vm->get('instance_uuid')),
            $this->translate('CPUs')   => $vm->get('hardware_numcpu'),
            $this->translate('MO Ref') => $this->linkToVCenter($vm->object()->get('moref')),
            $this->translate('Is Template') => $vm->get('template') === 'y'
                ? $this->translate('true')
                : $this->translate('false'),
            $this->translate('Path') => $path,
            $this->translate('Version')          => $vm->get('version'),

        ]);
    }

    protected function linkToVCenter($moRef)
    {
        try {
            $server = $this->vCenter->getFirstServer();
        } catch (NotFoundError $e) {
            return [
                Icon::create('warning-empty', [
                    'class' => 'red'
                ]),
                ' ',
                $this->translate('No related vServer has been configured')
            ];
        }
        return Html::tag('a', [
            'href' => sprintf(
                'https://%s/mob/?moid=%s',
                $server->get('host'),
                rawurlencode($moRef)
            ),
            'target' => '_blank',
            'title' => $this->translate('Jump to the Managed Object browser')
        ], $moRef);
    }
}
