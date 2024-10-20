import { Button, Modal } from '@newfold/ui-component-library';
import { XMarkIcon, CheckIcon } from '@heroicons/react/24/outline';
import { default as StagingSite } from '../stagingSite/';
import { default as ProductionSite } from '../productionSite/';
import { default as defaultText } from './defaultText';

/**
 * Staging Module
 * For use in brand plugin apps to display staging page
 * 
 * @param {*} props 
 * @returns 
 */
const Staging = ({methods, constants, Components, ...props}) => {
	const apiNamespace = '/newfold-staging/v1/';
	const [ isLoading, setIsLoading ] = methods.useState( true );
	const [ isThinking, setIsThinking ] = methods.useState( false );
	const [ isError, setIsError ] = methods.useState( false );
	const [ hasStaging, setHasStaging ] = methods.useState( null );
	const [ isProduction, setIsProduction ] = methods.useState( true );
	const [ creationDate, setCreationDate ] = methods.useState( null );
	const [ productionDir, setProductionDir ] = methods.useState( null );
	const [ productionUrl, setProductionUrl ] = methods.useState( null );
	const [ stagingDir, setStagingDir ] = methods.useState( null );
	const [ stagingUrl, setStagingUrl ] = methods.useState( null );
    const [ modalChildren, setModalChildren ] = methods.useState( <div /> );
    const [ modalOpen, setModalOpen ] = methods.useState( false );
	let notify = methods.useNotification();

	// set defaults if not provided
	constants.text = Object.assign(defaultText, constants.text);

    // Setup values from response
	const setup = ( response ) => {

		if ( response.hasOwnProperty( 'stagingExists' ) ) {
			setHasStaging( response.stagingExists );
		}
		if ( response.hasOwnProperty( 'currentEnvironment' ) ) {
			setIsProduction( response.currentEnvironment === 'production' );
		}
		if ( response.hasOwnProperty( 'productionDir' ) ) {
			setProductionDir( response.productionDir );
		}
		if ( response.hasOwnProperty( 'productionUrl' ) ) {
			setProductionUrl( response.productionUrl );
		}
		if ( response.hasOwnProperty( 'stagingDir' ) ) {
			setStagingDir( response.stagingDir );
		}
		if ( response.hasOwnProperty( 'stagingUrl' ) ) {
			setStagingUrl( response.stagingUrl );
		}
		if ( response.hasOwnProperty( 'creationDate' ) ) {
			setCreationDate( response.creationDate );
		}

	};

	const setError = ( error ) => {
		// console.log('setError', error);
		setIsLoading( false );
        setIsThinking( false );
		setIsError(true);
        makeNotice( 'error', constants.text.error, error, 'error' );
	};

	const catchError = (error) => {
		if ( error.hasOwnProperty( 'message' ) ) {
			setError(error.message);
		} else if ( error.hasOwnProperty( 'code' ) ) {
			setError(error.code);
		} else if ( error.hasOwnProperty( 'status' ) ) {
			setError(error.status);
		} else if ( error.hasOwnProperty( 'data' ) && error.data.hasOwnProperty('status') ) {
			setError(error.data.status);
		} else {
			setError(constants.text.unknownErrorMessage);
		}

	};

    const makeNotice = (id, title, description, variant="success", duration=false) => {
        notify.push(`staging-notice-${id}`, {
            title,
            description: (
                <span>
                    {description}
                </span>
            ),
            variant,
            autoDismiss: duration,
        });
    };

	/**
	 * on mount load staging data from module api
	 */
	methods.useEffect(() => {
		init();
	}, [] );

	const init = () => {
		// console.log('Init - Loading Staging Data');
		setIsError(false);
		setIsLoading(true);
		stagingApiFetch(
			'staging',
            null,
			'GET', 
			(response) => {
				// console.log('Init Staging Data:', response);
				// validate response data
				if ( response.hasOwnProperty('currentEnvironment') ) {
					//setup with fresh data
					setup( response );
				} else if ( response.hasOwnProperty('code') && response.code === 'error_response' ) {
					setError( response.message ); // report known error
				} else {
					setError( unknownErrorMsg ); // report unknown error
				}
				setIsThinking( false );
                setIsLoading( false );
			}
		);
	}

	const createStaging = () => {
		// console.log('create staging');
        makeNotice( 'creating', constants.text.working, constants.text.createNoticeStartText, 'info', 8000 );
		// setIsCreatingStaging(true);
		stagingApiFetch(
			'staging',
            null,
			'POST',
			(response) => {
				// console.log('Create Staging Callback', response);
				if ( response.hasOwnProperty('status') ) {
                    if ( response.status === 'success' ){
                        //setup with fresh data
                        setup( response );
                        makeNotice( 'created', constants.text.createNoticeCompleteText, response.message );
				    } else {
                        setError( response.message ); // report known error
                    }
				} else {
					setError( unknownErrorMsg ); // report unknown error
				}
				setIsThinking( false );
				// setIsCreatingStaging(false);
			}
		);
	};

	const deleteStaging = () => {
		// console.log('delete staging');
        makeNotice( 'deleting', constants.text.working, constants.text.deleteNoticeStartText, 'info', 8000 );
		stagingApiFetch(
			'staging',
            null, 
			'DELETE', 
			(response) => {
				// console.log('Delete staging callback', response);
				// validate response data
				if ( response.hasOwnProperty('status') ) {
                    if ( response.status === 'success' ){
                        // setup with fresh data
						setHasStaging( false );
                        makeNotice( 'deleted', constants.text.deleteNoticeCompleteText, response.message );
					} else {
						setError( response.message );
					}
				} else {
					setError( unknownErrorMsg ); // report unknown error
				}
				setIsThinking( false );
			}
		);
	};

	const clone = () => {
		// console.log('clone production to staging');
        makeNotice( 'cloning', constants.text.working, constants.text.cloneNoticeStartText, 'info', 8000 );
		stagingApiFetch(
			'staging/clone',
            null,
			'POST', 
			(response) => {
				// console.log('Clone Callback', response);
				// validate response data
				if ( response.hasOwnProperty('status') ) {
					// setup with fresh data
					if ( response.status === 'success' ){
						setHasStaging( true );
                        makeNotice( 'cloned', constants.text.cloneNoticeCompleteText, response.message );
					} else {
						setError( response.message );
					}
				} else {
					setError( unknownErrorMsg ); // report unknown error
				}
				setIsThinking( false );
			}
		);
	};

    const switchToStaging = () => {
        if ( !isProduction ) {
            // console.log('Already on staging.');
        } else {
            setModal(
                constants.text.switchToStaging,
                constants.text.switchToStagingDescription,
                switchToEnv,
                'staging',
                constants.text.switch
            );
        }
    };

    const switchToProduction = () => {
        if ( isProduction ) {
            // console.log('Already on production.');
        } else {
            setModal(
                constants.text.switchToProduction,
                constants.text.switchToProductionDescription,
                switchToEnv,
                'production',
                constants.text.switch
            );
        }
    };

	/**
	 * 
	 * @param {string} env One of 'staging' or 'production'
	 */
	const switchToEnv = ( env ) => {
		// console.log('switching to', env, `/switch-to?env=${ env }`);
		// setSwitchingTo( env );
        setIsThinking( true );
		if ( env === 'production' ) {
			makeNotice( 'switching', constants.text.working, constants.text.switchToProductionNoticeStartText, 'info', 8000 );
		} else {
			makeNotice( 'switching', constants.text.working, constants.text.switchToStagingNoticeStartText, 'info', 8000 );
		}

		stagingApiFetch(
			'staging/switch-to',
            {'env': env}, 
			'GET', 
			(response) => {
				// console.log('Switch Callback', response);
				// validate response data
				if ( response.hasOwnProperty( 'load_page' ) ) {
					window.location.href = response.load_page;
					// navigate(response.load_page);
					makeNotice( 'redirecting', constants.text.switching, constants.text.switchToProductionNoticeCompleteText, 'success', 8000 );
				} else if ( response.hasOwnProperty('status') && response.status === 'error' ) {
					setError(response.message);
				} else {
					setError( unknownErrorMsg ); // report unknown error
				}
			}
		);
	};

	/**
	 * 
	 * @param {string} type One of 'all', 'files', or 'db'
	 */
	const deployStaging = ( type ) => {
		// console.log('Deploy', type);
        makeNotice( 'deploying', constants.text.working, constants.text.deployNoticeStartText, 'info', 8000 );
		stagingApiFetch(
			'staging/deploy',
            {'type': type}, 
			'POST', 
			(response) => {
				// console.log('Deploy Callback', response);
				// validate response data
				if ( response.hasOwnProperty('status') ) {
					// setup with fresh data
					if ( response.status === 'success' ){
                        makeNotice( 'deployed', constants.text.deployNoticeCompleteText, response.message );
					} else {
						setError( response.message );
					}
				} else {
					setError( unknownErrorMsg ); // report unknown error
				}
				setIsThinking( false );
			}
		);
	};

	/**
	 * Wrapper method to interface with staging endpoints
	 *
	 * @param path append to the end of the apiNamespace
	 * @param method GET or POST, default GET
	 * @param thenCallback method to call in promise then
	 * @param passError setter for the error in component
	 * @return apiFetch promise
	 */
	const stagingApiFetch = (
        path = '', 
        qs = {}, 
        method = 'GET', 
        thenCallback, 
        errorCallback = catchError
    ) => {
        setIsThinking( true );
		return methods.apiFetch({
			url: methods.NewfoldRuntime.createApiUrl( apiNamespace + path, qs),
			method,
		}).then( (response) => {
			thenCallback( response );
		}).catch( (error) => {
			errorCallback( error );
		})
	};
    const modalClose = () => {
        setModalOpen(false);
    }
    const setModal = (title, description, callback, callbackParams=null, ctaText=constants.text.proceed) => {
        setModalChildren(
            <Modal.Panel>
                <Modal.Title className="nfd-text-2xl nfd-font-medium nfd-text-title">{title}</Modal.Title>
                <Modal.Description className="nfd-mt-8 nfd-mb-8">{description}</Modal.Description>
                <div className="nfd-flex nfd-justify-between nfd-items-center nfd-flex-wrap nfd-gap-3">
                    <Button
                        variant="error"
                        onClick={ () => { setModalOpen(false); }}
                        >
                        <XMarkIcon /> {constants.text.cancel}
                    </Button>
                    <Button
                        variant="primary"
                        onClick={ () => { 
                            setModalOpen(false);
                            callback(callbackParams);
                        }}
                        >
                        <CheckIcon /> {ctaText}
                    </Button>
                </div>
            </Modal.Panel>
        );
        setModalOpen(true);
    };

    const getClasses = () => {
		let theclasses = '';
        if ( isLoading ) {
            theclasses = 'is-loading';
        } else if ( isThinking ) {
            theclasses = 'is-thinking';
        } else if ( isError ) {
			theclasses = 'is-error';
		}
		return theclasses;
    };

	return (
		<Components.Page title={__('Staging', 'wp-plugin-bluehost')} className={methods.classnames('newfold-staging-page',  getClasses())}>
            <Components.SectionContainer className={'wppbh-app-staging-container'}>
				<div className={methods.classnames('newfold-staging-wrapper')}>
					<Components.SectionHeader
							title={constants.text.title}
							subTitle={constants.text.subTitle}
							className={'newfold-staging-header'}
						/>

						<Components.SectionContent separator={true} className={'newfold-staging-prod'}>
							<ProductionSite
								methods={methods}
								constants={constants}
								Components={Components}
								isProduction={isProduction}
								hasStaging={hasStaging}
								productionUrl={productionUrl}
								cloneMe={clone}
								switchToMe={switchToProduction}
								setModal={setModal}
							/>
						</Components.SectionContent>
						<Components.SectionContent className={'newfold-staging-staging'}>
							<StagingSite
								methods={methods}
								constants={constants}
								Components={Components}
								isProduction={isProduction}
								hasStaging={hasStaging}
								setHasStaging={setHasStaging}
								createMe={createStaging}
								deleteMe={deleteStaging}
								deployMe={deployStaging}
								switchToMe={switchToStaging}
								stagingUrl={stagingUrl}
								creationDate={creationDate}
								setModal={setModal}
							/>
						</Components.SectionContent>
						<Modal 
							isOpen={ modalOpen }
							onClose={ modalClose }
							children={ modalChildren }
						/>
				</div>
			</Components.SectionContainer>
		</Components.Page>
	);

};

export default Staging;