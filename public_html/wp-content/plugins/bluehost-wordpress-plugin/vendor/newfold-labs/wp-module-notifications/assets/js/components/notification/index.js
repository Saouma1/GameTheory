/**
 * Notification Component
 * For use in brand app to display each notification
 * 
 * @param {*} props 
 * @returns 
 */
const Notification = ({ id, content, methods, constants, ...props }) => {

    const onClose = ( event ) => {
        event.preventDefault();
        if ( event.keycode && ENTER !== event.keycode ) {
			return;
		}

        const noticeContainer = document.querySelector('[data-id="' + id +'"]');
        if ( noticeContainer ) {
            noticeContainer.classList.add('is-dismissed');
            methods.apiFetch( {
                url: `${constants.resturl}/newfold-notifications/v1/notifications/${id}`,
                method: 'DELETE'
            }).then( ( response ) => {
                methods.removeNotification(response.id);
            });
        }
    }

    /**
     * Send events to the WP REST API
     *
     * @param {Object} event The event data to be tracked.
     */
    const sendEvent = (event)  => {
        event.data = event.data || {};
        event.data.page = window.location.href;
        methods.apiFetch({
            path: `${constants.resturl}/newfold-data/v1/events/`,
            method: 'POST', 
            data: event
        });
    }

    const onButtonNavigate = ( event ) => {
        if ( event.keycode && ENTER !== event.keycode ) {
			return;
		}
        sendEvent({
            action: 'newfold-notification-click',
            data: {
                element: 'button',
                label: event.target.innerText,
                notificationId: id,
            }
        })
    }

    const onAnchorNavigate = ( event ) => {
        if ( event.keycode && ENTER !== event.keycode ) {
			return;
		}
        sendEvent({
            action: 'newfold-notification-click',
            data: {
                element: 'a',
                href: event.target.getAttribute('href'),
                label: event.target.innerText,
                notificationId: id,
            }
        })
    }

    methods.useEffect(() => {
        const noticeContainer   = document.querySelector('[data-id="' + id +'"]');
        const noticeCloser      = noticeContainer.querySelector('[data-action="close"]');
        const noticeButtons     = Array.from(noticeContainer.querySelectorAll('button'));
        const noticeAnchors     = Array.from(noticeContainer.querySelectorAll('a'));

        if (noticeButtons.length) {
            noticeButtons.forEach(
                button => {
                    if (button.getAttribute('data-action') !== 'close') {
                        button.addEventListener('click', onButtonNavigate);
                        button.addEventListener('onkeydown', onButtonNavigate);
                    }
                }
            )
        }

        if (noticeAnchors.length) {
            noticeAnchors.forEach(
                link => {
                    if (link.getAttribute('data-action') !== 'close') {
                        link.addEventListener('click', onAnchorNavigate);
                        link.addEventListener('onkeydown', onAnchorNavigate);
                    }
                }
            )
        }

        if (noticeCloser) {
            noticeCloser.addEventListener('click', onClose);
            noticeCloser.addEventListener('onkeydown', onClose);
        }
        
        return () => {
            if (noticeButtons.length) {
                noticeButtons.forEach(
					button => {
						if (button.getAttribute('data-action') !== 'close') {
                            button.removeEventListener('click', onButtonNavigate);
                            button.removeEventListener('onkeydown', onButtonNavigate);
						}
					}
				)
            }
            if (noticeAnchors.length) {
				noticeAnchors.forEach(
					link => {
						if (link.getAttribute('data-action') !== 'close') {
                            link.removeEventListener('click', onAnchorNavigate);
                            link.removeEventListener('onkeydown', onAnchorNavigate);
						}
					}
				)
            }
            if (noticeCloser) {
                noticeCloser.removeEventListener('click', onClose);
                noticeCloser.removeEventListener('onkeydown', onClose);
            }
        }
    }, [id]);

    return (
        <div 
            id={`notification-${id}`}
            data-id={id}
            className='newfold-notification'
            dangerouslySetInnerHTML={ {__html: content} }
        />
    );
};

export default Notification;