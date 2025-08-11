<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Module_Registry {
    /** @var BRPB_Base_Module[] */
    private array $modules = [];

    public function __construct(){ $this->register_all_modules(); }

    private function register_all_modules(): void {
        $module_classes = [
            'BRPB_Row_Module',
            'BRPB_Column_Module',

            'BRPB_Block_1_Module',
            'BRPB_Big_Grid_Flex_1_Module',
            'BRPB_Post_Grid_Module',

            'BRPB_Ad_Box_Module',
            'BRPB_Author_Box_Module',
            'BRPB_Raw_HTML_Module',
            'BRPB_Weather_Module',

            'BRPB_Header_Date_Module',
            'BRPB_Header_Logo_Module',

            'BRPB_Button_Module',
            'BRPB_Call_To_Action_Module',
        ];
        foreach($module_classes as $cls){
            if(class_exists($cls)){
                $m = new $cls();
                $this->modules[$m->get_id()] = $m;
            }
        }
        do_action('brpb_register_modules',$this);
    }

    public function register_module( BRPB_Base_Module $module ): void {
        $this->modules[$module->get_id()] = $module;
    }

    public function get_module(string $id): ?BRPB_Base_Module {
        return $this->modules[$id] ?? null;
    }

    public function get_all_modules_by_group(): array {
        $grouped = [];
        foreach($this->modules as $m){
            $g = $m->get_group();
            if(!isset($grouped[$g])) $grouped[$g]=[];
            $grouped[$g][] = $m;
        }
        ksort($grouped);
        return $grouped;
    }
}
