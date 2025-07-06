
document.addEventListener('DOMContentLoaded', () => {

    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 1000,
            once: true,
        });
    }

    const gameTabButtons = document.querySelectorAll('.game-tab-button');
    gameTabButtons.forEach(button => {
        button.addEventListener('click', () => {
            gameTabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
        });
    });

    const stepItems = document.querySelectorAll('.step-item');
    const detailContents = document.querySelectorAll('.step-detail-content');
    const progressFill = document.querySelector('.progress-fill');

    function updateProgressLine(activeIndex) {
        if (!progressFill) return;
        const totalSteps = stepItems.length;
        if (totalSteps <= 1) {
            progressFill.style.width = '0%';
            return;
        }
        const progressPercentage = (activeIndex / (totalSteps - 1)) * 100;
        progressFill.style.width = `${progressPercentage}%`;
    }

    function showStepContent(stepId) {
        let activeIndex = -1;
        stepItems.forEach((item, index) => {
            item.classList.remove('active');
            if (item.dataset.stepId === stepId) {
                activeIndex = index;
            }
        });
        const clickedStep = document.querySelector(`.step-item[data-step-id="${stepId}"]`);
        if (clickedStep) {
            clickedStep.classList.add('active');
        }
        detailContents.forEach(content => {
            content.classList.remove('active');
        });
        const targetDetail = document.getElementById(`detail-${stepId}`);
        if (targetDetail) {
            targetDetail.classList.add('active');
        }
        updateProgressLine(activeIndex);
    }

    if (stepItems.length > 0) {
        const firstStepId = stepItems[0].dataset.stepId;
        showStepContent(firstStepId);
    }

    stepItems.forEach(item => {
        item.addEventListener('click', () => {
            const stepId = item.dataset.stepId;
            showStepContent(stepId);
        });
    });

    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.navbar .nav-links a');
    let scrollTimeout;

    const handleScroll = () => {
        let currentSectionId = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (window.pageYOffset >= sectionTop - 150) {
                currentSectionId = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.href && link.href.endsWith('#' + currentSectionId)) {
                link.classList.add('active');
            }
        });
    };

    window.addEventListener('scroll', () => {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(handleScroll, 100);
    });

});