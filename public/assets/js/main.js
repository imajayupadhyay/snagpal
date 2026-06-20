(function(){
  var reduce=window.matchMedia('(prefers-reduced-motion:reduce)').matches;
  var data=window.portfolioData||{};
  function esc(value){
    return String(value).replace(/[&<>"']/g,function(char){
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char];
    });
  }

  /* recommendations data — ROLE-BASED PLACEHOLDERS.
     Replace text + designations with real, consented endorsements. */
  var recs=Array.isArray(data.recommendations)?data.recommendations:[
    {q:"Among the clearest voices on turning AI-governance principle into public-sector practice.",w:"Secretary, Department of Information Technology"},
    {q:"Rare command of both the engineering and the policy. Exactly the combination government needs right now.",w:"Chief Engineer, Power Utility"},
    {q:"Brought genuine discipline and transparency to how we think about digital systems and procurement.",w:"Director, Public-Sector Undertaking"},
    {q:"A principled, dependable voice on responsible technology for critical national infrastructure.",w:"Joint Secretary, Ministry"},
    {q:"Translates national AI policy into something teams can actually implement and stand behind.",w:"Programme Director, e-Governance"}
  ];
  var mq=document.getElementById('marquee');
  function card(r){return '<figure class="rec"><p>'+esc(r.q)+'</p><figcaption class="who">'+esc(r.w)+'</figcaption></figure>';}
  if(mq){mq.innerHTML=(recs.map(card).join(''))+(recs.map(card).join(''));} // duplicate for loop

  /* topic ticker — site focus areas, duplicated for a seamless loop */
  var topics=Array.isArray(data.topics)?data.topics:["Digital Transformation","Critical Infrastructure","Public-Sector IT",
    "AI Governance","Responsible AI","Cyber Security","e-Governance","Data Privacy",
    "Power & Water Systems","Public Trust","Policy to Practice"];
  var tt=document.getElementById('topicsTrack');
  if(tt){
    var item=function(t){return '<span class="topic"><span class="t">'+esc(t)+'</span><span class="sp">\u2726</span></span>';};
    var html=topics.map(item).join('');
    tt.innerHTML=html+html;
  }

  /* loader */
  var loader=document.getElementById('loader'),countEl=document.getElementById('count'),bar=document.getElementById('lbar');
  function reveals(){document.querySelectorAll('.hero .reveal, .about-hero .reveal, .cohort-detail-hero .reveal').forEach(function(el,i){setTimeout(function(){el.classList.add('in');},90*i);});
    document.querySelectorAll('.hero .clip, .about-hero .clip, .cohort-detail-hero .clip').forEach(function(el,i){setTimeout(function(){el.classList.add('in');},150+130*i);});
    var hi=document.getElementById('heroImg');if(hi)hi.classList.add('in');}
  function finish(){document.body.classList.remove('loading');loader.classList.add('done');setTimeout(reveals,260);}
  if(reduce){countEl.textContent='100';bar.style.width='100%';finish();}
  else{var n=0;var iv=setInterval(function(){n+=Math.floor(Math.random()*10)+4;if(n>=100){n=100;clearInterval(iv);}countEl.textContent=n;bar.style.width=n+'%';if(n===100)setTimeout(finish,340);},90);}

  document.getElementById('yr').textContent=new Date().getFullYear();

  /* in her words — rotating quotes (draft statements, review/approve) */
  var quotes=Array.isArray(data.quotes)?data.quotes:[
    "Technology in government is never just technical: every system we build is something the public ultimately depends on.",
    "Responsible AI in the public sector cannot stop at principles. It has to become practice a civil servant can apply on a Tuesday morning.",
    "The task is not to deploy more technology, but to govern it well, with transparency, accountability, and public trust as the measure."
  ];
  var qt=document.getElementById('qtext'),qf=document.getElementById('qfloat'),qd=document.getElementById('qdots'),qi=0,qTimer=null;
  function qSetDots(){Array.prototype.forEach.call(qd.children,function(d,j){d.className=(j===qi?'on':'');});}
  function qShow(i){qf.style.opacity=0;setTimeout(function(){qi=i;qt.textContent=quotes[qi];qSetDots();qf.style.opacity=1;},500);}
  function qNext(){qShow((qi+1)%quotes.length);}
  function qReset(){if(qTimer){clearInterval(qTimer);qTimer=setInterval(qNext,6500);}}
  if(qt&&qd){
    quotes.forEach(function(_,i){var b=document.createElement('button');if(i===0)b.className='on';
      b.setAttribute('aria-label','Quote '+(i+1));b.addEventListener('click',function(){qShow(i);qReset();});qd.appendChild(b);});
    if(!reduce){qTimer=setInterval(qNext,6500);}
  }

  /* reveal on scroll */
  var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target);}});},{threshold:0.14,rootMargin:'0px 0px -7% 0px'});
  document.querySelectorAll('section .reveal, section .clip').forEach(function(el){io.observe(el);});

  /* theme toggle */
  var root=document.documentElement,tbtn=document.getElementById('themeToggle');
  function applyTheme(t){
    root.setAttribute('data-theme',t);
    try{localStorage.setItem('theme',t);}catch(e){}
    if(tbtn){tbtn.setAttribute('aria-pressed',t==='dark');
      tbtn.setAttribute('aria-label',t==='dark'?'Switch to light mode':'Switch to dark mode');}
  }
  applyTheme(root.getAttribute('data-theme')||'light');
  if(tbtn){tbtn.addEventListener('click',function(){
    applyTheme(root.getAttribute('data-theme')==='dark'?'light':'dark');});}

  /* mobile navigation */
  var menuButton=document.getElementById('menuToggle');
  var mobileMenu=document.getElementById('mobileMenu');
  function setMenu(open){
    if(!menuButton||!mobileMenu){
      return;
    }

    mobileMenu.hidden=!open;
    menuButton.setAttribute('aria-expanded',open?'true':'false');
    menuButton.setAttribute('aria-label',open?'Close menu':'Open menu');
    document.body.classList.toggle('menu-open',open);
  }

  function menuIsOpen(){
    return !!(mobileMenu&&!mobileMenu.hidden);
  }

  if(menuButton&&mobileMenu){
    menuButton.addEventListener('click',function(){
      setMenu(!menuIsOpen());
    });

    mobileMenu.querySelectorAll('a,button').forEach(function(item){
      item.addEventListener('click',function(){
        setMenu(false);
      });
    });

    document.addEventListener('click',function(event){
      if(menuIsOpen()&&nav&&!nav.contains(event.target)){
        setMenu(false);
      }
    });

    document.addEventListener('keydown',function(event){
      if(event.key==='Escape'&&menuIsOpen()){
        setMenu(false);
        menuButton.focus();
      }
    });

    window.addEventListener('resize',function(){
      if(window.innerWidth>600&&menuIsOpen()){
        setMenu(false);
      }
    });
  }

  /* meeting booking modal */
  var modal=document.getElementById('meetingModal');
  if(modal){
    var openers=document.querySelectorAll('[data-schedule-open]');
    var closers=modal.querySelectorAll('[data-schedule-close]');
    var dialog=modal.querySelector('.meeting-dialog');
    var form=modal.querySelector('.meeting-form');
    var alertBox=modal.querySelector('[data-meeting-alert]');
    var dateInput=modal.querySelector('[data-meeting-date]');
    var slotSelect=modal.querySelector('[data-meeting-slot]');
    var submitButton=form?form.querySelector('.meeting-submit'):null;
    var slots=Array.isArray(data.meetingSlots)?data.meetingSlots:[];
    var initialSlot=slotSelect?slotSelect.value:'';
    var lastFocus=null;

    function findSlot(id){
      return slots.filter(function(slot){return String(slot.id)===String(id);})[0]||null;
    }

    function showMeetingMessage(type,messages){
      if(!alertBox){
        return;
      }

      var list=Array.isArray(messages)?messages:[messages];
      alertBox.className='meeting-alert '+(type==='success'?'success':'error');
      alertBox.setAttribute('role',type==='success'?'status':'alert');
      alertBox.innerHTML=list.map(function(message){return '<p>'+esc(message||'Unable to complete the request.')+'</p>';}).join('');
      alertBox.hidden=false;
    }

    function setSubmitting(isSubmitting){
      if(!submitButton){
        return;
      }

      if(isSubmitting){
        submitButton.dataset.label=submitButton.textContent;
        submitButton.textContent='Booking...';
        submitButton.disabled=true;
        return;
      }

      submitButton.textContent=submitButton.dataset.label||'Book Meeting';
      submitButton.disabled=false;
    }

    function fillSlots(){
      if(!dateInput||!slotSelect){
        return;
      }

      var selected=slotSelect.value||initialSlot;
      var date=dateInput.value;
      slotSelect.innerHTML='';

      var placeholder=document.createElement('option');
      placeholder.value='';
      placeholder.textContent=date?'Select a slot':'Select a date first';
      slotSelect.appendChild(placeholder);

      if(!date){
        slotSelect.disabled=true;
        return;
      }

      slotSelect.disabled=false;
      var matching=slots.filter(function(slot){return slot.date===date;});

      if(!matching.length){
        var empty=document.createElement('option');
        empty.value='';
        empty.textContent='No slots open on this date';
        slotSelect.appendChild(empty);
        slotSelect.value='';
        return;
      }

      matching.forEach(function(slot){
        var option=document.createElement('option');
        option.value=slot.id;
        option.textContent=slot.time_label;
        slotSelect.appendChild(option);
      });

      if(findSlot(selected)&&findSlot(selected).date===date){
        slotSelect.value=selected;
      }
    }

    function syncSlots(nextSlots){
      if(Array.isArray(nextSlots)){
        slots=nextSlots;
        data.meetingSlots=nextSlots;
      }

      fillSlots();
    }

    if(dateInput&&slotSelect&&initialSlot&&!dateInput.value){
      var selectedSlot=findSlot(initialSlot);
      if(selectedSlot){
        dateInput.value=selectedSlot.date;
      }
    }

    function focusFirst(){
      var target=dateInput||modal.querySelector('input:not([type="hidden"]):not(.hp-field),select,textarea,button');
      if(target){
        target.focus();
      }
    }

    function openModal(){
      lastFocus=document.activeElement;
      modal.hidden=false;
      modal.setAttribute('aria-hidden','false');
      document.body.classList.add('modal-open');
      fillSlots();
      setTimeout(focusFirst,30);
    }

    function closeModal(){
      modal.hidden=true;
      modal.setAttribute('aria-hidden','true');
      document.body.classList.remove('modal-open');
      if(lastFocus&&typeof lastFocus.focus==='function'){
        lastFocus.focus();
      }
    }

    openers.forEach(function(opener){
      opener.addEventListener('click',function(event){
        event.preventDefault();
        openModal();
      });
    });

    closers.forEach(function(closer){
      closer.addEventListener('click',closeModal);
    });

    if(dateInput){
      dateInput.addEventListener('change',function(){
        initialSlot='';
        fillSlots();
      });
    }

    document.addEventListener('keydown',function(event){
      if(event.key==='Escape'&&!modal.hidden){
        closeModal();
      }
    });

    if(dialog){
      dialog.addEventListener('click',function(event){
        event.stopPropagation();
      });
    }

    if(form&&window.fetch){
      form.addEventListener('submit',function(event){
        event.preventDefault();

        setSubmitting(true);

        fetch(form.action,{
          method:'POST',
          body:new FormData(form),
          headers:{
            'Accept':'application/json',
            'X-Requested-With':'XMLHttpRequest'
          },
          credentials:'same-origin'
        })
          .then(function(response){
            return response.json().then(function(payload){
              payload.httpOk=response.ok;
              return payload;
            });
          })
          .then(function(payload){
            syncSlots(payload.slots);

            if(payload.ok){
              showMeetingMessage('success',[payload.message||'Your meeting request has been received and is pending confirmation.']);
              initialSlot='';
              form.reset();
              fillSlots();
              if(alertBox){
                alertBox.focus&&alertBox.focus();
              }
              return;
            }

            showMeetingMessage('error',payload.errors||['Unable to book that slot. Please try again.']);
          })
          .catch(function(){
            showMeetingMessage('error',['Unable to submit the booking without refreshing. Please try again.']);
          })
          .finally(function(){
            setSubmitting(false);
          });
      });
    }

    fillSlots();

    if(modal.getAttribute('data-auto-open')==='true'){
      openModal();
    }
  }

  /* nav shrink */
  var nav=document.getElementById('nav');
  window.addEventListener('scroll',function(){nav.classList.toggle('shrink',window.scrollY>50);},{passive:true});

  /* cohorts slider */
  (function(){
    var track=document.getElementById('cohortsTrack');
    if(!track) return;
    var arrows=document.querySelector('.cohorts-arrows');
    var prev=document.querySelector('[data-cohorts="prev"]');
    var next=document.querySelector('[data-cohorts="next"]');
    var cards=track.querySelectorAll('.cohort-card');
    var dots=document.querySelectorAll('#cohortsDots .cohorts-dot');
    function step(){
      var gap=parseFloat(getComputedStyle(track).columnGap)||16;
      return cards.length?Math.round(cards[0].getBoundingClientRect().width+gap):Math.round(track.clientWidth*0.8);
    }
    function activeIndex(){
      var max=track.scrollWidth-track.clientWidth;
      if(track.scrollLeft>=max-1) return cards.length-1;
      return Math.max(0,Math.min(cards.length-1,Math.round(track.scrollLeft/step())));
    }
    function goTo(i){
      if(!cards[i]) return;
      track.scrollTo({left:cards[i].offsetLeft-cards[0].offsetLeft,behavior:'smooth'});
    }
    function update(){
      var max=track.scrollWidth-track.clientWidth;
      if(arrows) arrows.classList.toggle('is-hidden',max<=2);
      if(prev) prev.disabled=track.scrollLeft<=1;
      if(next) next.disabled=track.scrollLeft>=max-1;
      var ai=activeIndex();
      for(var d=0;d<dots.length;d++){dots[d].classList.toggle('on',d===ai);}
    }
    if(prev) prev.addEventListener('click',function(){track.scrollBy({left:-step(),behavior:'smooth'});});
    if(next) next.addEventListener('click',function(){track.scrollBy({left:step(),behavior:'smooth'});});
    for(var i=0;i<dots.length;i++){(function(idx){dots[idx].addEventListener('click',function(){goTo(idx);});})(i);}
    track.addEventListener('scroll',update,{passive:true});
    window.addEventListener('resize',update);
    update();
  })();
})();
