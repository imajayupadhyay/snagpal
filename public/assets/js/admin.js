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

  /* ---- paste cleaning -------------------------------------------------
     Pasted content (Word, Google Docs, other sites, or another browser tab)
     arrives wrapped in spans, font tags, classes, and inline styles. Dropping
     it wholesale loses the paragraphs; keeping it wholesale drags foreign
     styling into the site. So it is rebuilt against the same allow-list the
     PHP sanitiser uses, keeping structure and alignment and nothing else. */
  var PASTE_ALLOWED={P:1,H2:1,H3:1,H4:1,STRONG:1,B:1,EM:1,I:1,U:1,UL:1,OL:1,LI:1,BLOCKQUOTE:1,A:1,BR:1};
  var PASTE_DROPPED={SCRIPT:1,STYLE:1,META:1,LINK:1,NOSCRIPT:1,SVG:1,IMG:1,IFRAME:1,OBJECT:1,EMBED:1,INPUT:1,BUTTON:1,SELECT:1,TEXTAREA:1};
  var PASTE_TEXT_BLOCK={P:1,H2:1,H3:1,H4:1,BLOCKQUOTE:1};
  var PASTE_BLOCK={P:1,H2:1,H3:1,H4:1,LI:1,BLOCKQUOTE:1,UL:1,OL:1};
  var PASTE_BLOCK_SELECTOR='p,div,h1,h2,h3,h4,h5,h6,ul,ol,li,blockquote,table,tr,pre';

  function pasteAlignment(node){
    var value=(node.style&&node.style.textAlign)||node.getAttribute('align')||'';
    value=String(value).toLowerCase().trim();

    return /^(left|right|center|justify)$/.test(value)?value:'';
  }

  function cleanPastedInto(source,target){
    var nodes=[].slice.call(source.childNodes);

    nodes.forEach(function(node){
      if(node.nodeType===3){
        target.appendChild(document.createTextNode(node.nodeValue.replace(/ /g,' ')));
        return;
      }

      if(node.nodeType!==1||PASTE_DROPPED[node.tagName]){
        return;
      }

      // A <div> is only a paragraph when it holds text. Layout wrappers that
      // contain their own blocks must be unwrapped, or every block inside them
      // gets flattened into one paragraph.
      var tag=node.tagName;

      if(tag==='DIV'){
        tag=node.querySelector(PASTE_BLOCK_SELECTOR)?'':'P';
      }

      if(!tag){
        cleanPastedInto(node,target);
        return;
      }

      var parent=target.tagName||'';

      // Unknown wrappers (span, font, table cells, …) keep their text but lose
      // themselves, so a paste never nests foreign structure into the page.
      if(!PASTE_ALLOWED[tag]){
        cleanPastedInto(node,target);
        return;
      }

      // A paragraph inside a paragraph is invalid; flatten it instead.
      if(PASTE_TEXT_BLOCK[tag]&&PASTE_TEXT_BLOCK[parent]){
        cleanPastedInto(node,target);
        return;
      }

      // A list item only means something inside a list.
      if(tag==='LI'&&parent!=='UL'&&parent!=='OL'){
        tag='P';
      }

      var element=document.createElement(tag);

      if(tag==='A'){
        var href=node.getAttribute('href')||'';

        if(/^(https?:\/\/|mailto:|tel:|\/|#)/i.test(href)&&href.length<=500){
          element.setAttribute('href',href);
        }
      }

      if(PASTE_BLOCK[tag]){
        var align=pasteAlignment(node);

        if(align){
          element.style.textAlign=align;
        }
      }

      cleanPastedInto(node,element);
      target.appendChild(element);
    });
  }

  function sanitizePastedHtml(html){
    // Parse into an inert document. Assigning to a live element's innerHTML
    // would fetch images and fire their inline handlers (an onerror in pasted
    // markup is enough to run script), which must never happen here.
    var parsed;

    try{
      parsed=new DOMParser().parseFromString(html,'text/html');
    }catch(error){
      return plainTextToHtml(String(html).replace(/<[^>]*>/g,''));
    }

    var clean=document.createElement('div');
    cleanPastedInto(parsed.body||parsed.documentElement,clean);

    return clean.innerHTML;
  }

  function plainTextToHtml(text){
    return String(text).replace(/\r\n?/g,'\n').split(/\n\s*\n/).map(function(block){
      var escaped=block.trim()
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/\n/g,'<br>');

      return escaped===''?'':'<p>'+escaped+'</p>';
    }).join('');
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

    surface.addEventListener('paste',function(event){
      var clipboard=event.clipboardData||window.clipboardData;

      if(!clipboard){
        return;
      }

      var html=clipboard.getData('text/html');
      var replacement=html?sanitizePastedHtml(html):plainTextToHtml(clipboard.getData('text/plain')||'');

      if(!replacement){
        return;
      }

      event.preventDefault();
      document.execCommand('insertHTML',false,replacement);
      sync();
    });

    surface.addEventListener('input',sync);
    surface.addEventListener('blur',sync);

    var form=editor.closest('form');

    if(form){
      form.addEventListener('submit',sync);
    }

    sync();
  }

  // Pressing Enter should start a new <p>, not the <div> Chrome defaults to,
  // so what the editor produces matches what the sanitiser keeps.
  try{
    document.execCommand('defaultParagraphSeparator',false,'p');
  }catch(error){}

  document.querySelectorAll('[data-rich-editor]').forEach(initRichEditor);

  /* notification dropdown */
  (function(){
    var centers=[].slice.call(document.querySelectorAll('.notification-center'));

    if(!centers.length){
      return;
    }

    document.addEventListener('click',function(event){
      centers.forEach(function(center){
        if(!center.contains(event.target)){
          center.removeAttribute('open');
        }
      });
    });

    document.addEventListener('keydown',function(event){
      if(event.key==='Escape'){
        centers.forEach(function(center){center.removeAttribute('open');});
      }
    });
  })();

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
