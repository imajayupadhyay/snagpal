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
  if(loader&&countEl&&bar){
    if(reduce){countEl.textContent='100';bar.style.width='100%';finish();}
    else{var n=0;var iv=setInterval(function(){n+=Math.floor(Math.random()*10)+4;if(n>=100){n=100;clearInterval(iv);}countEl.textContent=n;bar.style.width=n+'%';if(n===100)setTimeout(finish,340);},90);}
  }else{
    document.body.classList.remove('loading');
    reveals();
  }

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

  /* give-a-recommendation modal */
  var recModal=document.getElementById('recommendationModal');
  if(recModal){
    var recOpeners=document.querySelectorAll('[data-recommend-open]');
    var recClosers=recModal.querySelectorAll('[data-recommend-close]');
    var recDialog=recModal.querySelector('.meeting-dialog');
    var recForm=recModal.querySelector('#recommendationForm');
    var recAlert=recModal.querySelector('[data-recommend-alert]');
    var recSubmit=recForm?recForm.querySelector('.meeting-submit'):null;
    var recLastFocus=null;

    function showRecMessage(type,messages){
      if(!recAlert){
        return;
      }

      var list=Array.isArray(messages)?messages:[messages];
      recAlert.className='meeting-alert '+(type==='success'?'success':'error');
      recAlert.setAttribute('role',type==='success'?'status':'alert');
      recAlert.innerHTML=list.map(function(message){return '<p>'+esc(message||'Unable to complete the request.')+'</p>';}).join('');
      recAlert.hidden=false;
    }

    function setRecSubmitting(isSubmitting){
      if(!recSubmit){
        return;
      }

      if(isSubmitting){
        recSubmit.dataset.label=recSubmit.textContent;
        recSubmit.textContent='Submitting...';
        recSubmit.disabled=true;
        return;
      }

      recSubmit.textContent=recSubmit.dataset.label||'Submit Recommendation';
      recSubmit.disabled=false;
    }

    function openRecModal(){
      recLastFocus=document.activeElement;
      recModal.hidden=false;
      recModal.setAttribute('aria-hidden','false');
      document.body.classList.add('modal-open');
      setTimeout(function(){
        var target=recModal.querySelector('input:not([type="hidden"]):not(.hp-field),select,textarea,button');
        if(target){target.focus();}
      },30);
    }

    function closeRecModal(){
      recModal.hidden=true;
      recModal.setAttribute('aria-hidden','true');
      document.body.classList.remove('modal-open');
      if(recLastFocus&&typeof recLastFocus.focus==='function'){
        recLastFocus.focus();
      }
    }

    recOpeners.forEach(function(opener){
      opener.addEventListener('click',function(event){
        event.preventDefault();
        openRecModal();
      });
    });

    recClosers.forEach(function(closer){
      closer.addEventListener('click',closeRecModal);
    });

    document.addEventListener('keydown',function(event){
      if(event.key==='Escape'&&!recModal.hidden){
        closeRecModal();
      }
    });

    if(recDialog){
      recDialog.addEventListener('click',function(event){
        event.stopPropagation();
      });
    }

    if(recForm&&window.fetch){
      recForm.addEventListener('submit',function(event){
        event.preventDefault();

        setRecSubmitting(true);

        fetch(recForm.action,{
          method:'POST',
          body:new FormData(recForm),
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
            if(payload.ok){
              showRecMessage('success',[payload.message||'Thank you. Your recommendation has been submitted.']);
              recForm.reset();
              if(recAlert){recAlert.focus&&recAlert.focus();}
              return;
            }

            showRecMessage('error',payload.errors||['Unable to submit your recommendation. Please try again.']);
          })
          .catch(function(){
            showRecMessage('error',['Unable to submit without refreshing. Please try again.']);
          })
          .finally(function(){
            setRecSubmitting(false);
          });
      });
    }

    if(recModal.getAttribute('data-auto-open')==='true'){
      openRecModal();
    }
  }

  /* upcoming event registration modal */
  var eventRegModal=document.getElementById('eventRegistrationModal');
  if(eventRegModal){
    var eventRegOpeners=document.querySelectorAll('[data-event-registration-open]');
    var eventRegClosers=eventRegModal.querySelectorAll('[data-event-registration-close]');
    var eventRegDialog=eventRegModal.querySelector('.meeting-dialog');
    var eventRegForm=eventRegModal.querySelector('#eventRegistrationForm');
    var eventRegAlert=eventRegModal.querySelector('[data-event-registration-alert]');
    var eventRegSubmit=eventRegForm?eventRegForm.querySelector('.meeting-submit'):null;
    var eventRegLastFocus=null;

    function showEventRegMessage(type,messages){
      if(!eventRegAlert){
        return;
      }

      var list=Array.isArray(messages)?messages:[messages];
      eventRegAlert.className='meeting-alert '+(type==='success'?'success':'error');
      eventRegAlert.setAttribute('role',type==='success'?'status':'alert');
      eventRegAlert.innerHTML=list.map(function(message){return '<p>'+esc(message||'Unable to complete the registration.')+'</p>';}).join('');
      eventRegAlert.hidden=false;
    }

    function setEventRegSubmitting(isSubmitting){
      if(!eventRegSubmit){
        return;
      }

      if(isSubmitting){
        eventRegSubmit.dataset.label=eventRegSubmit.textContent;
        eventRegSubmit.textContent='Submitting...';
        eventRegSubmit.disabled=true;
        return;
      }

      eventRegSubmit.textContent=eventRegSubmit.dataset.label||'Submit Registration';
      eventRegSubmit.disabled=false;
    }

    function openEventRegModal(){
      eventRegLastFocus=document.activeElement;
      eventRegModal.hidden=false;
      eventRegModal.setAttribute('aria-hidden','false');
      document.body.classList.add('modal-open');
      setTimeout(function(){
        var target=eventRegModal.querySelector('input:not([type="hidden"]):not(.hp-field),select,textarea,button');
        if(target){target.focus();}
      },30);
    }

    function closeEventRegModal(){
      eventRegModal.hidden=true;
      eventRegModal.setAttribute('aria-hidden','true');
      document.body.classList.remove('modal-open');
      if(eventRegLastFocus&&typeof eventRegLastFocus.focus==='function'){
        eventRegLastFocus.focus();
      }
    }

    eventRegOpeners.forEach(function(opener){
      opener.addEventListener('click',function(event){
        event.preventDefault();
        openEventRegModal();
      });
    });

    eventRegClosers.forEach(function(closer){
      closer.addEventListener('click',closeEventRegModal);
    });

    document.addEventListener('keydown',function(event){
      if(event.key==='Escape'&&!eventRegModal.hidden){
        closeEventRegModal();
      }
    });

    if(eventRegDialog){
      eventRegDialog.addEventListener('click',function(event){
        event.stopPropagation();
      });
    }

    if(eventRegForm&&window.fetch){
      eventRegForm.addEventListener('submit',function(event){
        event.preventDefault();

        setEventRegSubmitting(true);

        fetch(eventRegForm.action,{
          method:'POST',
          body:new FormData(eventRegForm),
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
            if(payload.ok){
              showEventRegMessage('success',[payload.message||'Thank you. Your registration has been received.']);
              eventRegForm.reset();
              if(eventRegAlert){eventRegAlert.focus&&eventRegAlert.focus();}
              return;
            }

            showEventRegMessage('error',payload.errors||['Unable to submit your registration. Please try again.']);
          })
          .catch(function(){
            showEventRegMessage('error',['Unable to submit without refreshing. Please try again.']);
          })
          .finally(function(){
            setEventRegSubmitting(false);
          });
      });
    }

    if(eventRegModal.getAttribute('data-auto-open')==='true'){
      openEventRegModal();
    }
  }

  /* cohort category tabs */
  var cohortTabs=document.querySelector('[data-cohort-tabs]');
  var cohortGrid=document.querySelector('[data-cohort-grid]');
  if(cohortTabs&&cohortGrid){
    var cohortCards=cohortGrid.querySelectorAll('[data-cohort-category]');
    var cohortEmpty=document.querySelector('[data-cohort-empty]');

    cohortTabs.addEventListener('click',function(event){
      var tab=event.target.closest('[data-cohort-tab]');
      if(!tab){return;}

      var category=tab.getAttribute('data-cohort-tab');
      var visibleCount=0;

      cohortTabs.querySelectorAll('[data-cohort-tab]').forEach(function(btn){
        var isActive=btn===tab;
        btn.classList.toggle('is-active',isActive);
        btn.setAttribute('aria-selected',isActive?'true':'false');
      });

      cohortCards.forEach(function(card){
        var matches=category==='all'||card.getAttribute('data-cohort-category')===category;
        card.classList.toggle('is-hidden',!matches);
        if(matches){visibleCount++;}
      });

      if(cohortEmpty){
        cohortEmpty.hidden=visibleCount!==0;
      }
    });
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

/* cohort/event video posters: fall back when a thumbnail is missing, load the player on click.
   Kept outside the main IIFE so a failure anywhere else can never stop videos from playing. */
(function(){
    document.querySelectorAll('.cohort-poster[data-poster-fallback]').forEach(function(img){
      img.addEventListener('error',function(){
        var next=img.getAttribute('data-poster-fallback');
        img.removeAttribute('data-poster-fallback');
        if(next) img.src=next;
      });
    });

    document.addEventListener('click',function(event){
      var button=event.target.closest('[data-cohort-play]');
      if(!button) return;
      var type=button.getAttribute('data-embed-type');
      var src=button.getAttribute('data-embed-src');
      if(!src) return;
      var title=button.getAttribute('data-embed-title')||'Video';
      var poster=button.getAttribute('data-embed-poster')||'';
      var player;
      if(type==='video'){
        player=document.createElement('video');
        player.className='cohort-video';
        player.controls=true;player.autoplay=true;player.playsInline=true;
        if(poster) player.poster=poster;
        var source=document.createElement('source');
        source.src=src;
        player.appendChild(source);
      }else{
        player=document.createElement('iframe');
        player.className='cohort-frame';
        player.src=src;
        player.title=title;
        player.setAttribute('referrerpolicy','strict-origin-when-cross-origin');
        player.setAttribute('allow','accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
        player.setAttribute('allowfullscreen','');
      }
      button.parentNode.replaceChild(player,button);
      if(type==='video'){var played=player.play();if(played&&played.catch)played.catch(function(){});}
    });
})();

/* photo gallery carousels (cohorts/events without a video) + fullscreen viewer.
   Kept in its own IIFE so an error elsewhere can never break the galleries.
   The track is CSS scroll-snap, so swipe already works without this file. */
(function(){
  var galleries=document.querySelectorAll('[data-gallery]');
  if(!galleries.length) return;

  var lightbox=null,lbImage=null,lbCaption=null,lbCount=null,lbPrev=null,lbNext=null;
  var lbPhotos=[],lbIndex=0,lbOpener=null;

  function photosOf(gal){
    var out=[];
    gal.querySelectorAll('.gal-image').forEach(function(img){
      out.push({src:img.currentSrc||img.src,alt:img.alt||'',caption:img.getAttribute('data-gal-caption')||''});
    });
    return out;
  }

  function buildLightbox(){
    if(lightbox) return;
    lightbox=document.createElement('div');
    lightbox.className='gal-lightbox';
    lightbox.setAttribute('role','dialog');
    lightbox.setAttribute('aria-modal','true');
    lightbox.setAttribute('aria-label','Photo viewer');
    lightbox.innerHTML=
      '<div class="gal-lightbox-stage">'+
        '<img class="gal-lightbox-image" src="" alt="">'+
        '<button type="button" class="gal-lightbox-btn gal-lightbox-prev" aria-label="Previous photo"><span aria-hidden="true">&#8249;</span></button>'+
        '<button type="button" class="gal-lightbox-btn gal-lightbox-next" aria-label="Next photo"><span aria-hidden="true">&#8250;</span></button>'+
        '<button type="button" class="gal-lightbox-btn gal-lightbox-close" aria-label="Close photo viewer"><span aria-hidden="true">&#10005;</span></button>'+
      '</div>'+
      '<div class="gal-lightbox-foot">'+
        '<p class="gal-lightbox-caption"></p>'+
        '<span class="gal-lightbox-count"></span>'+
      '</div>';
    document.body.appendChild(lightbox);
    lbImage=lightbox.querySelector('.gal-lightbox-image');
    lbCaption=lightbox.querySelector('.gal-lightbox-caption');
    lbCount=lightbox.querySelector('.gal-lightbox-count');
    lbPrev=lightbox.querySelector('.gal-lightbox-prev');
    lbNext=lightbox.querySelector('.gal-lightbox-next');

    lbPrev.addEventListener('click',function(){showLb(lbIndex-1);});
    lbNext.addEventListener('click',function(){showLb(lbIndex+1);});
    lightbox.querySelector('.gal-lightbox-close').addEventListener('click',closeLb);
    lightbox.addEventListener('click',function(event){
      if(event.target===lightbox||event.target.classList.contains('gal-lightbox-stage')) closeLb();
    });
    document.addEventListener('keydown',function(event){
      if(!lightbox.classList.contains('on')) return;
      if(event.key==='Escape'){closeLb();}
      else if(event.key==='ArrowLeft'){showLb(lbIndex-1);}
      else if(event.key==='ArrowRight'){showLb(lbIndex+1);}
    });
  }

  function showLb(index){
    if(!lbPhotos.length) return;
    lbIndex=(index+lbPhotos.length)%lbPhotos.length;
    var photo=lbPhotos[lbIndex];
    lbImage.src=photo.src;
    lbImage.alt=photo.alt;
    lbCaption.textContent=photo.caption;
    lbCaption.hidden=!photo.caption;
    lbCount.textContent=(lbIndex+1)+' / '+lbPhotos.length;
    var single=lbPhotos.length<2;
    lbPrev.hidden=single;
    lbNext.hidden=single;
    lbCount.hidden=single;
  }

  function openLb(photos,index,opener){
    if(!photos.length) return;
    buildLightbox();
    lbPhotos=photos;
    lbOpener=opener||null;
    showLb(index);
    lightbox.classList.add('on');
    document.body.style.overflow='hidden';
    lightbox.querySelector('.gal-lightbox-close').focus();
  }

  function closeLb(){
    if(!lightbox) return;
    lightbox.classList.remove('on');
    document.body.style.overflow='';
    if(lbOpener&&lbOpener.focus){lbOpener.focus();}
    lbOpener=null;
  }

  galleries.forEach(function(gal){
    var track=gal.querySelector('[data-gal-track]');
    if(!track) return;
    var slides=track.querySelectorAll('.gal-slide');
    var dots=gal.querySelectorAll('[data-gal-dot]');
    var counter=gal.querySelector('[data-gal-count]');
    var prev=gal.querySelector('[data-gal="prev"]');
    var next=gal.querySelector('[data-gal="next"]');
    if(!slides.length) return;

    function current(){
      var width=track.clientWidth||1;
      return Math.max(0,Math.min(slides.length-1,Math.round(track.scrollLeft/width)));
    }
    function goTo(index){
      var target=Math.max(0,Math.min(slides.length-1,index));
      track.scrollTo({left:target*track.clientWidth,behavior:'smooth'});
    }
    function update(){
      var index=current();
      for(var i=0;i<dots.length;i++){dots[i].classList.toggle('on',i===index);}
      if(counter) counter.textContent=(index+1)+' / '+slides.length;
      if(prev) prev.disabled=index<=0;
      if(next) next.disabled=index>=slides.length-1;
    }

    if(prev) prev.addEventListener('click',function(){goTo(current()-1);});
    if(next) next.addEventListener('click',function(){goTo(current()+1);});
    for(var d=0;d<dots.length;d++){
      (function(idx){dots[idx].addEventListener('click',function(){goTo(idx);});})(d);
    }
    track.addEventListener('scroll',update,{passive:true});
    window.addEventListener('resize',update);
    track.addEventListener('keydown',function(event){
      if(event.key==='ArrowLeft'){event.preventDefault();goTo(current()-1);}
      else if(event.key==='ArrowRight'){event.preventDefault();goTo(current()+1);}
    });

    track.addEventListener('click',function(event){
      var slide=event.target.closest('.gal-slide');
      if(!slide) return;
      openLb(photosOf(gal),parseInt(slide.getAttribute('data-gal-slide'),10)||0,track);
    });

    update();
  });
})();
