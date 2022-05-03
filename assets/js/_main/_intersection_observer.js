function loadIntersectionObserver() {
    const triggers = document.querySelector('.intersection-observer-trigger');

    if(triggers !== null) {
        const options = {
            threshold: 0.01
        };
    
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if(entry.isIntersecting === true) {
                    $(entry.target).trigger('change');
                }
            })
        }, options);
        
        observer.observe(triggers);
    }
}