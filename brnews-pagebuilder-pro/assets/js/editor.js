(function($){
'use strict';

var BRPB_Editor={
  layoutData:[],
  activeElementId:null,
  isSaving:false,
  init:function(){
    this.layoutData = brpb_editor_data.initial_layout || [];
    this.cache(); this.bind();
  },
  cache:function(){
    this.panel=$('#brpb-panel-elements');
    this.iframe=$('#brpb-canvas-iframe');
    this.inspector=$('#brpb-inspector-content');
    this.btnSave=$('#brpb-save-button');
  },
  bind:function(){
    var self=this;
    this.panel.on('click','.brpb-element-drag-item',function(e){
      e.preventDefault();
      var t=$(this).data('element-type');
      var el={id:'brpb-el-'+Math.random().toString(36).substr(2,9),typeId:t,children:[],settings:{}};
      self.layoutData.push(el);
      self.selectElement(el.id);
      alert('Elemento adicionado. Configure e clique em "Salvar Layout".');
    });
    this.btnSave.on('click',this.save.bind(this));
    window.addEventListener('message',this.onMsg.bind(this));
    this.inspector.on('input change','input,select,textarea',this.onInspectorChange.bind(this));
    this.inspector.on('click','.brpb-delete-element',this.deleteActive.bind(this));
  },
  onMsg:function(e){
    if(e.origin!==window.location.origin) return;
    var m=e.data; if(!m||!m.action) return;
    if(m.action==='brpb_canvas_ready'){ /* could live preview */ }
    if(m.action==='brpb_element_clicked'){ this.selectElement(m.elementId); }
  },
  selectElement:function(id){
    this.activeElementId=id;
    var el=this.findById(id); if(!el) return;
    this.loadInspector(el);
    this.iframe[0].contentWindow.postMessage({action:'brpb_highlight_element',elementId:id},'*');
  },
  loadInspector:function(el){
    var self=this;
    this.inspector.html('<p class="loading">Carregando...</p>');
    $.post(brpb_editor_data.ajax_url,{
      action:'brpb_get_element_controls',
      nonce:brpb_editor_data.nonce,
      element_type:el.typeId,
      element_data:JSON.stringify(el)
    }).done(function(r){
      if(r.success){ self.inspector.html(r.data.html); }
      else{ self.inspector.html('<p>Erro ao carregar controles.</p>'); }
    }).fail(function(){ self.inspector.html('<p>Falha de comunicação.</p>'); });
  },
  onInspectorChange:function(e){
    if(!this.activeElementId) return;
    var $c=$(e.currentTarget);
    var key=$c.data('setting-id'); if(!key) return;
    var val;
    if($c.is(':checkbox')) val=$c.is(':checked');
    else if($c.is('select[multiple]')) val=$c.val()||[];
    else val=$c.val();

    var path=key.split('.');
    var el=this.findById(this.activeElementId); if(!el) return;
    var cur=el.settings;
    for(var i=0;i<path.length-1;i++){ if(cur[path[i]]===undefined) cur[path[i]]={}; cur=cur[path[i]]; }
    cur[path[path.length-1]]=val;
  },
  deleteActive:function(){
    if(!this.activeElementId) return;
    if(!confirm('Remover este elemento?')) return;
    this.layoutData=this.removeById(this.layoutData,this.activeElementId);
    this.activeElementId=null;
    this.inspector.html('<p>Selecione um elemento para editar.</p>');
  },
  save:function(){
    var self=this;
    if(this.isSaving) return;
    this.isSaving=true;
    var txt=this.btnSave.text(); this.btnSave.text(brpb_editor_data.i18n.saving).prop('disabled',true);
    $.post(brpb_editor_data.ajax_url,{
      action:'brpb_save_layout',
      nonce:brpb_editor_data.nonce,
      post_id:brpb_editor_data.post_id,
      layout_data:JSON.stringify(this.layoutData)
    }).done(function(r){
      if(r.success){ self.btnSave.text(brpb_editor_data.i18n.saved); }
      else{ alert('Erro: '+(r.data&&r.data.message?r.data.message:'falha')); self.btnSave.text(txt); }
    }).fail(function(){ alert('Falha de rede.'); self.btnSave.text(txt); })
    .always(function(){ setTimeout(function(){ self.btnSave.prop('disabled',false).text(txt); self.isSaving=false; $('#brpb-canvas-iframe').attr('src',$('#brpb-canvas-iframe').attr('src')); },1200); });
  },
  findById:function(id,arr){
    arr=arr||this.layoutData;
    for(var i=0;i<arr.length;i++){ if(arr[i].id===id) return arr[i]; if(arr[i].children&&arr[i].children.length){var f=this.findById(id,arr[i].children); if(f) return f;} }
    return null;
  },
  removeById:function(arr,id){
    return arr.filter(function(x){return x.id!==id}).map(function(x){ if(x.children&&x.children.length){ x.children=this.removeById(x.children,id);} return x;}.bind(this));
  }
};

$(function(){ if($('#brpb-editor-wrapper').length){ BRPB_Editor.init(); } });

})(jQuery);
