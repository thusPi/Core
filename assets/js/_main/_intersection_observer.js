function loadIntersectionObserver() {
    const triggers = document.querySelector('.intersection-observer-trigger');

    if(triggers !== null) {
        const options = {
            threshold: 0.01
        };
    
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if(entry.isIntersecting === true) {
                    $(entry.target).trigger('change');
                }
            })
        }, options);
        
        observer.observe(triggers);
    }
}