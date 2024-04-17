jQuery(function( $ ){		
  gsap.registerPlugin(ScrollTrigger);

  let sections = gsap.utils.toArray("main > section");
  let listItem = gsap.utils.toArray("aside nav li");

  sections.forEach((section, index) => {
    ScrollTrigger.create({
      trigger: section,
      start: 'top center',
      end: 'center top',
      toggleClass: { targets: listItem[index], className: "active" }
    });
  });
    
  gsap.fromTo('aside > nav', { }, {
    ease: 'none',
    scrollTrigger: {
      trigger: 'aside',
      start: 'top 100px',
      end: 'bottom 30%',
      scrub: true,
      toggleClass: 'fixed'
    }
  });
});