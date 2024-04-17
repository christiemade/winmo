jQuery(function( $ ){		
  gsap.registerPlugin(ScrollTrigger);

  let sections = gsap.utils.toArray("main > section");
  let listItem = gsap.utils.toArray("aside nav li");

  sections.forEach((section, index) => {
    ScrollTrigger.create({
      trigger: section,
      markers: true,
      start: 'top bottom',
      end: 'bottom bottom',
      toggleClass: { targets: listItem[index], className: "active" }
    });
  });
    
  gsap.fromTo('aside > nav', { position: 'relative' }, {
    position: "fixed",
    ease: 'none',
    scrollTrigger: {
      trigger: 'aside',
      start: 'top 100px',
      scrub: true,
      toggleClass: 'fixed'
    }
  });
});