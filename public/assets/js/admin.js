(function(){
  function cleanBlockHtml(html){
    return html
      .replace(/<div><br><\/div>/gi,'')
      .replace(/<p><br><\/p>/gi,'')
      .replace(/&nbsp;/gi,' ')
      .trim();
  }

  function collectParagraphs(surface){
    var blocks=surface.querySelectorAll('p,div');
    var values=[];

    if(blocks.length){
      blocks.forEach(function(block){
        var html=cleanBlockHtml(block.innerHTML);
        var text=block.textContent.replace(/\u00a0/g,' ').trim();

        if(html!==''&&text!==''){
          values.push(html);
        }
      });
    }else{
      var html=cleanBlockHtml(surface.innerHTML);

      if(html!==''){
        values.push(html);
      }
    }

    return values.join('\n\n');
  }

  function initRichEditor(editor){
    var source=document.getElementById(editor.getAttribute('data-editor-for'));
    var surface=editor.querySelector('.wysiwyg-surface');
    var mode=editor.getAttribute('data-rich-mode')||'single';

    if(!source||!surface){
      return;
    }

    function sync(){
      source.value=mode==='paragraphs'
        ? collectParagraphs(surface)
        : cleanBlockHtml(surface.innerHTML);
    }

    editor.querySelectorAll('[data-command]').forEach(function(button){
      button.addEventListener('click',function(){
        var command=button.getAttribute('data-command');
        var value=button.getAttribute('data-value')||null;

        surface.focus();

        if(command==='createLink'){
          value=window.prompt('Enter the link URL','https://');

          if(!value){
            return;
          }
        }

        if(command==='formatBlock'&&value){
          value='<'+value+'>';
        }

        document.execCommand(command,false,value);
        sync();
      });
    });

    surface.addEventListener('input',sync);
    surface.addEventListener('blur',sync);

    var form=editor.closest('form');

    if(form){
      form.addEventListener('submit',sync);
    }

    sync();
  }

  document.querySelectorAll('[data-rich-editor]').forEach(initRichEditor);

  /* mobile sidebar drawer */
  (function(){
    var sidebar=document.getElementById('adminSidebar');
    var toggle=document.querySelector('[data-sidebar-toggle]');
    var backdrop=document.querySelector('[data-sidebar-backdrop]');
    if(!sidebar||!toggle) return;
    function setOpen(open){
      sidebar.classList.toggle('open',open);
      if(backdrop) backdrop.classList.toggle('show',open);
      document.body.classList.toggle('sidebar-open',open);
      toggle.setAttribute('aria-expanded',open?'true':'false');
    }
    toggle.addEventListener('click',function(){setOpen(!sidebar.classList.contains('open'));});
    if(backdrop) backdrop.addEventListener('click',function(){setOpen(false);});
    document.addEventListener('keydown',function(e){if(e.key==='Escape') setOpen(false);});
  })();

  /* repeat row builders */
  (function(){
    function initBuilder(builder){
      var list=builder.querySelector('[data-repeat-list]');
      var template=builder.querySelector('template[data-repeat-template]');
      var add=builder.querySelector('[data-repeat-add]');

      if(!list||!template||!add){
        return;
      }

      function rows(){
        return [].slice.call(list.querySelectorAll('[data-repeat-row]'));
      }

      function updateNumbers(){
        rows().forEach(function(row,index){
          row.querySelectorAll('[data-index-label]').forEach(function(label){
            label.textContent=String(index+1);
          });
        });
      }

      add.addEventListener('click',function(){
        var key='new_'+Date.now()+'_'+Math.floor(Math.random()*1000);
        var holder=document.createElement('div');
        holder.innerHTML=template.innerHTML.replace(/__INDEX__/g,key).trim();
        var row=holder.firstElementChild;

        if(row){
          list.appendChild(row);
          updateNumbers();
          var first=row.querySelector('input,textarea,select');
          if(first) first.focus();
        }
      });

      list.addEventListener('click',function(event){
        var remove=event.target.closest('[data-repeat-remove]');

        if(!remove){
          return;
        }

        var row=remove.closest('[data-repeat-row]');

        if(row){
          row.remove();
          updateNumbers();
        }
      });

      updateNumbers();
    }

    document.querySelectorAll('[data-repeat-builder]').forEach(initBuilder);
  })();

  /* homepage editor: section jump nav + scroll-spy */
  (function(){
    var jump=document.getElementById('sectionJump');
    if(!jump) return;
    var panels=[].slice.call(document.querySelectorAll('.content-form .form-panel'));
    if(!panels.length) return;
    var links=[];
    panels.forEach(function(panel,i){
      if(!panel.id) panel.id='section-'+(i+1);
      var eyebrow=panel.querySelector('.panel-head .eyebrow');
      var heading=panel.querySelector('.panel-head h2');
      var label=(eyebrow&&eyebrow.textContent.trim())||(heading&&heading.textContent.trim())||('Section '+(i+1));
      var a=document.createElement('a');
      a.href='#'+panel.id;
      a.textContent=label;
      a.setAttribute('data-target',panel.id);
      a.addEventListener('click',function(e){
        e.preventDefault();
        var y=panel.getBoundingClientRect().top+window.scrollY-78;
        window.scrollTo({top:y,behavior:'smooth'});
      });
      jump.appendChild(a);
      links.push(a);
    });
    function setActive(id){
      links.forEach(function(l){
        var on=l.getAttribute('data-target')===id;
        l.classList.toggle('is-active',on);
        if(on) l.scrollIntoView({block:'nearest',inline:'nearest'});
      });
    }
    if('IntersectionObserver' in window){
      var io=new IntersectionObserver(function(entries){
        entries.forEach(function(en){if(en.isIntersecting) setActive(en.target.id);});
      },{rootMargin:'-44% 0px -50% 0px',threshold:0});
      panels.forEach(function(p){io.observe(p);});
    }
    setActive(panels[0].id);
  })();
})();
