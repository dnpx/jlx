<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class BRPB_Css_Generator {

    private array $css_rules = [ 'desktop'=>[], 'tablet'=>[], 'mobile'=>[] ];
    private const BREAKPOINTS = [ 'tablet'=>'1024px', 'mobile'=>'767px' ];

    public function generate_for_layout( array $layout_data ): string {
        $this->css_rules = [ 'desktop'=>[], 'tablet'=>[], 'mobile'=>[] ];
        $this->walk($layout_data);
        return $this->to_string();
    }

    private function walk(array $els): void {
        foreach($els as $el){
            $selector = '#' . sanitize_html_class($el['id'] ?? '');
            $settings = $el['settings'] ?? [];
            if(isset($settings['style_tab'])) $this->style_tab($selector, $settings['style_tab']);
            if(!empty($el['children'])) $this->walk($el['children']);
        }
    }

    private function style_tab(string $sel, array $st): void {
        if(!empty($st['padding'])) $this->dimensions($sel,'padding',$st['padding']);
        if(!empty($st['margin'])) $this->dimensions($sel,'margin',$st['margin']);
        if(!empty($st['border_radius'])) $this->dimensions($sel,'border-radius',$st['border_radius']);
        if(!empty($st['background'])) $this->background($sel,$st['background']);
        if(!empty($st['border'])) $this->border($sel,$st['border']);
    }

    private function dimensions(string $sel,string $prop,array $vals): void {
        foreach(['desktop','tablet','mobile'] as $dev){
            if(empty($vals[$dev]) || !is_array($vals[$dev])) continue;
            $v = $vals[$dev];
            $top = isset($v['top']) ? intval($v['top']).'px' : '0';
            $right = isset($v['right']) ? intval($v['right']).'px' : '0';
            $bottom = isset($v['bottom']) ? intval($v['bottom']).'px' : '0';
            $left = isset($v['left']) ? intval($v['left']).'px' : '0';
            $val = trim("$top $right $bottom $left");
            if($val !== '0 0 0 0'){
                $this->add_rule($dev,$sel,$prop,$val);
            }
        }
    }

    private function background(string $sel, array $v): void {
        if(!empty($v['color'])) $this->add_rule('desktop',$sel,'background-color',sanitize_hex_color($v['color']));
        if(!empty($v['image_url'])) $this->add_rule('desktop',$sel,'background-image','url('.esc_url_raw($v['image_url']).')');
    }

    private function border(string $sel, array $v): void {
        $type = isset($v['type']) ? sanitize_text_field($v['type']) : 'none';
        if($type === 'none') return;
        $width = isset($v['width']) ? intval($v['width']).'px' : '1px';
        $color = isset($v['color']) ? sanitize_hex_color($v['color']) : '#000';
        $this->add_rule('desktop',$sel,'border',"$width $type $color");
    }

    private function add_rule(string $dev,string $sel,string $prop,string $val): void {
        if(!isset($this->css_rules[$dev][$sel])) $this->css_rules[$dev][$sel]=[];
        $this->css_rules[$dev][$sel][$prop]=$val;
    }

    private function to_string(): string {
        $out = "/**\\n * CSS gerado por Brnews Pagebuilder PRO em ".date('Y-m-d H:i:s')."\\n */\\n\\n";

        // desktop
        foreach($this->css_rules['desktop'] as $sel=>$rules){
            $out .= $sel." {\\n";
            foreach($rules as $p=>$v){ $out .= "  ".$p.": ".$v.";\\n"; }
            $out .= "}\\n\\n";
        }
        // tablet
        if(!empty($this->css_rules['tablet'])){
            $out .= "@media (max-width: ".self::BREAKPOINTS['tablet'].") {\\n";
            foreach($this->css_rules['tablet'] as $sel=>$rules){
                $out .= "  ".$sel." {\\n";
                foreach($rules as $p=>$v){ $out .= "    ".$p.": ".$v.";\\n"; }
                $out .= "  }\\n";
            }
            $out .= "}\\n\\n";
        }
        // mobile
        if(!empty($this->css_rules['mobile'])){
            $out .= "@media (max-width: ".self::BREAKPOINTS['mobile'].") {\\n";
            foreach($this->css_rules['mobile'] as $sel=>$rules){
                $out .= "  ".$sel." {\\n";
                foreach($rules as $p=>$v){ $out .= "    ".$p.": ".$v.";\\n"; }
                $out .= "  }\\n";
            }
            $out .= "}\\n";
        }
        return $out;
    }
}
