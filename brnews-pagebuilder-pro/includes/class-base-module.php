<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class BRPB_Base_Module {
    abstract public function get_id(): string;
    abstract public function get_name(): string;
    abstract public function get_icon(): string;
    abstract public function get_group(): string;
    abstract public function get_controls(): array;
    abstract public function render( array $settings, string $element_id, array $children ): void;
}
