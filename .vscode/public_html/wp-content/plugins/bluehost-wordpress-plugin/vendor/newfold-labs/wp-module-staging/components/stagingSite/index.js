import { Button, Radio, Select } from '@newfold/ui-component-library';
import { ArrowPathIcon, TrashIcon } from '@heroicons/react/24/outline';

/**
 * Staging Site
 * For use in brand plugin apps to display staging page
 * 
 * @param {*} props 
 * @returns 
 */
const StagingSite = ({
    methods,
    constants,
    Components,
    hasStaging, 
    isProduction, 
    createMe,
    deployMe,
    deleteMe,
    switchToMe,
    stagingUrl,
    setModal,
    creationDate,
}) => {
    const [deployOption, setDeployOption] = methods.useState( 'all' );

    return (
        <Components.SectionSettings
            title={constants.text.stagingSiteTitle}
            description={!hasStaging ? constants.text.noStagingSite :
                <Radio
                    checked={isProduction !== true}
                    label={isProduction ? constants.text.notCurrentlyEditing : constants.text.currentlyEditing }
                    id="newfold-staging-toggle"
                    name="newfold-staging-selector"
                    value="staging"
                    onChange={() => {
                        switchToMe();
                    }}
                />
            }
        >
            <div className="nfd-flex nfd-justify-between nfd-items-center nfd-flex-wrap nfd-gap-3">
                {!hasStaging &&
                    <div className="nfd-flex nfd-justify-end nfd-w-full">
                        <Button 
                            variant="secondary"
                            id="staging-create-button"
                            onClick={() => { 
                            createMe()
                        }}>
                            {constants.text.createStagingSite}
                        </Button>
                    </div>
                }
                {hasStaging &&
                    <>
                        <div>
                            {stagingUrl}
                            <dl className="nfd-flex nfd-justify-between nfd-items-center nfd-flex-wrap nfd-gap-3">
                                <dt>{constants.text.created}:</dt>
                                <dd>{creationDate}</dd>
                            </dl>
                        </div>
                        <div className="nfd-flex nfd-gap-1.5 nfd-relative">
                            <Select
                                disabled={ isProduction ? true : false }
                                id="newfold-staging-select"
                                name="newfold-staging"
                                className="nfd-w-48"
                                value={deployOption}
                                onChange={(value) => { setDeployOption(value) }}
                                options={[
                                    {
                                        label: constants.text.deployAll,
                                        value: 'all'
                                    },
                                    {
                                        label: constants.text.deployFiles,
                                        value: 'files'
                                    },
                                    {
                                        label: constants.text.deployDatabase,
                                        value: 'db'
                                    }
                                ]}
                            />
                            <Button
                                disabled={isProduction ? true : false }
                                id="staging-deploy-button"
                                title={constants.text.deploySite}
                                onClick={() => { 
                                    // console.log('Open confirm modal: Deploy stagin option to production');
                                    setModal(
                                        constants.text.deployConfirm,
                                        constants.text.deployDescription,
                                        deployMe,
                                        deployOption,
                                        constants.text.deploy
                                    )
                                }}
                            >
                                <ArrowPathIcon />
                            </Button>

                            <Button
                                disabled={isProduction ? false : true }
                                variant="error"
                                id="staging-delete-button"
                                title={constants.text.deleteSite}
                                onClick={() => { 
                                    // console.log('Open confirm modal: Delete stagin option to production');
                                    setModal(
                                        constants.text.deleteConfirm,
                                        constants.text.deleteDescription,
                                        deleteMe,
                                        null,
                                        constants.text.delete
                                    )
                                }}
                            >
                                <TrashIcon />
                            </Button>
                        </div>
                    </>
                }

            </div>
        </Components.SectionSettings>
    );
};

export default StagingSite;