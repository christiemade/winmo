jQuery(function( $ ){		

  gsap.registerPlugin(ScrollTrigger);

  gsap.fromTo('aside > nav', { position: 'realtive' }, {
    position: "sticky",
    ease: 'none',
    scrollTrigger: {
      trigger: 'aside',
      start: 'top top',
      scrub: true,
      markers: {startColor: "white", endColor: "white", fontSize: "18px", fontWeight: "bold", indent: 20}
    }
  });
});