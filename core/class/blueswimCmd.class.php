<?php
require_once dirname(__FILE__) . '/../../core/php/blueswim.inc.php';

class blueswimCmd extends cmd {

    // Exécution d'une commande action
    public function execute($_options = array()) {
        $eqLogic = $this->getEqLogic();

        if ($this->getLogicalId() == 'refresh') {
            blueswim::updateInfo($eqLogic->getId());
            return;
        }

        // Ici tu peux ajouter d'autres actions si besoin
    }

    // Méthode appelée après la création/sauvegarde d'une commande
    public function postSave() {
        $eqLogic = $this->getEqLogic();

        $cmdList = [
            'temperature' => ['type'=>'info','subType'=>'numeric','unite'=>'°C','min'=>0,'max'=>50,'name'=>'Temperature'],
            'ph' => ['type'=>'info','subType'=>'numeric','unite'=>'','min'=>5,'max'=>10,'name'=>'Ph'],
            'orp' => ['type'=>'info','subType'=>'numeric','unite'=>'mV','min'=>200,'max'=>1000,'name'=>'Redox']
        ];

        // Ajouter salinity et conductivity uniquement pour blueplus
        if ($eqLogic->getConfiguration('device') == 'blueplus') {
            $cmdList['salinity'] = ['type'=>'info','subType'=>'numeric','unite'=>'g/l','min'=>0,'max'=>10,'name'=>'Salinity'];
            $cmdList['conductivity'] = ['type'=>'info','subType'=>'numeric','unite'=>'µS','min'=>0,'max'=>15000,'name'=>'Conductivity'];
        }

        // Ajouter refresh (action)
        $cmdList['refresh'] = ['type'=>'action','subType'=>'other','unite'=>'','name'=>'Refresh'];

        foreach ($cmdList as $id => $cfg) {
            $cmd = $eqLogic->getCmd(null, $id);
            if (!is_object($cmd)) {
                $cmd = new blueswimCmd();
                $cmd->setLogicalId($id);
                $cmd->setName(__($cfg['name'], __FILE__));
                $cmd->setUnite($cfg['unite'] ?? '');
                $cmd->setIsVisible(1);
                $cmd->setIsHistorized($cfg['type']=='info'?1:0);
            }
            $cmd->setType($cfg['type']);
            $cmd->setSubType($cfg['subType']);
            $cmd->setEqLogic_id($eqLogic->getId());
            $cmd->setDisplay('generic_type','GENERIC');
            if ($cfg['type']=='info') {
                $cmd->setConfiguration('minValue', $cfg['min']);
                $cmd->setConfiguration('maxValue', $cfg['max']);
            }
            $cmd->save();
        }
    }
}
