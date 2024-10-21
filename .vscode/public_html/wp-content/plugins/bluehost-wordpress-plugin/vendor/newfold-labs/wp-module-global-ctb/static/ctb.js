{

    const loadCtb = (e) => {
        const ctbId = e.target.getAttribute('data-ctb-id');
        const destinationUrl = e.target.getAttribute('href');
        disableLink(ctbId);
        //open modal
        const modal = openModal(e, ctbId);
        const modalWindow = modal.querySelector('.global-ctb-modal-content');
        const modalLoader = modal.querySelector('.global-ctb-loader');
        //track click
        ctbClickEvent(e, ctbId);

        // handle click to receive ctb iframe url
        window.fetch(
            `${window.NewfoldRuntime.restUrl}newfold-ctb/v2/ctb/${ctbId}`,
            {
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.NewfoldRuntime.restNonce,
                },
            }
        )
            .then(response => {
                enableLink(ctbId);
                if (response.ok) {
                    return response.json();
                } else {
                    throw Error(response.statusText);
                }
            })
            .then(data => {
                // enable close button
                modalWindow.querySelector('.global-ctb-modal-close').style.display = 'flex';
                // set the content to an iframe of specified url
                let iframe = document.createElement('iframe');
                iframe.src = data.url;
                modalWindow.replaceChild(iframe, modalLoader);
            })
            .catch(error => {
                displayError(modalWindow, error);
                // dont display error, just close modal and open fallback link
                closeModal();
                removeCtbAttrs(ctbId);
                window.open(destinationUrl, '_blank', 'noopener noreferrer');
            });
    }

    const ctbClickEvent = (e, ctbId) => {
        window.wp.apiFetch({
			url: window.nfdgctb.eventendpoint,
			method: 'POST', 
			data: {
                action: 'ctb_modal_opened',
                data: {
                    'label_key': 'ctb_id',
                    'ctb_id': ctbId,
                    'brand': window.nfdgctb.brand,
                    'context': determineContext(e),
                    'page': window.location.href
                }
            }
		});
    }    

    // walk up the dom to find context of button
    const determineContext = (e) => {
        // but first check for a ctb-context attribute on target
        if ( e.target.hasAttribute('data-ctb-context') ) {
            return e.target.getAttribute('data-ctb-context');
        }
        // if target has marketplace-item parent
        if ( e.target.closest('.marketplace-item') ) {
            return 'marketplace-item';
        }
        // if target has notification parent
        if ( e.target.closest( '.newfold-notifications-wrapper' ) ) {
            return 'notification';
        }
        // TODO - add context check for ecommerce ctb
        // if target has app root parent (from ui library)
        if ( e.target.closest( '.nfd-root' ) ) {
            return 'plugin-app';
        }
        // TODO - add context check for yoast plugin ctb
        // if outside plugin app
        return 'external';
    }

    // disable link
    const disableLink = ( ctbId ) => {
        const ctbButton = document.querySelector('[data-ctb-id="' + ctbId + '"]');
        ctbButton.setAttribute('disabled', 'true');
    }

    // reenable link
    const enableLink = ( ctbId ) => {
        const ctbButton = document.querySelector('[data-ctb-id="' + ctbId + '"]');
        ctbButton.removeAttribute('disabled');
    }

    // Remove attributes to avoid continued errors
    const removeCtbAttrs = ( ctbId ) => {
        const ctbButton = document.querySelector('[data-ctb-id="' + ctbId + '"]');
        if ( ctbButton ) {
            ctbButton.removeAttribute('data-ctb-id');
            ctbButton.removeAttribute('data-action');
        }
    }

    const openModal = (e, ctbId) => {
        let modalContent = `
		<div class="global-ctb-modal">
			<div class="global-ctb-modal-overlay" data-a11y-dialog-destroy></div>
			<div role="document" class="global-ctb-modal-content">
				<div class="global-ctb-modal-close" data-a11y-dialog-destroy style="display:none;">✕</div>
				<div class="global-ctb-loader"></div>
			</div>
		</div>
		`;
        let ctbContainer = document.getElementById('nfd-global-ctb-container');
        if (ctbContainer) {
            ctbContainer.innerHTML = modalContent
        } else {
            ctbContainer = document.createElement('div');
            ctbContainer.setAttribute('id', 'nfd-global-ctb-container');
            ctbContainer.innerHTML = modalContent;
            ctbContainer.target.insertAdjacentElement('afterend', nfd - global - ctb - container);
        }

        ctbContainer.setAttribute('data-ctb-id', ctbId);
        ctbmodal = new A11yDialog(ctbContainer);
        ctbmodal.show();
        document.querySelector('body').classList.add('noscroll');

        return ctbContainer;
    }

    const closeModal = () => {
        ctbmodal.destroy();
        document.querySelector('body').classList.remove('noscroll');
    }

    const displayError = (modalWindow, error) => {
        let message = (error === 'purchase') ? 'complete the transaction' : 'load the product information';
        modalWindow.innerHTML = `<div style="text-align:center;">
            <h3>${error}</h3>
			<p>Sorry, we are unable to ${message} at this time.</p>
			<button class="components-button bluehost is-primary" data-a11y-dialog-destroy>Cancel</button>
		</div>`;
        //remove ctb attributes from button so the user can click the link
        removeCtbAttrs();
    }

    /**
     * Can access global CTB - checks corresponding NewfoldRuntime capability
     */
    const supportsGlobalCTB = () => {
        return (
            "NewfoldRuntime" in window &&
            "capabilities" in window.NewfoldRuntime &&
            "canAccessGlobalCTB" in window.NewfoldRuntime.capabilities &&
            window.NewfoldRuntime.capabilities.canAccessGlobalCTB === true
        );
    }

    window.addEventListener(
        'load',
        () => {
            document.getElementById('wpwrap').addEventListener('click', function (event) {
                // has ctb data attribute and is not disabled
                if (event.target.dataset.ctbId && event.target.getAttribute('disabled') !== 'true') {
                    // can access global ctb
                    if (supportsGlobalCTB()) {
                        event.preventDefault();
                        loadCtb(event);
                    } else {
                        // do nothing, fallback to href
                    }
                }
                // close button
                if (event.target.hasAttribute('data-a11y-dialog-destroy')) {
                    closeModal();
                }
            });
        }
    );

    window.addEventListener('message', function (event) {
        if (!event.origin.includes('hiive')) {
            return;
        }
        if (event.data === 'closeModal') {
            closeModal();
        }
    });

}
