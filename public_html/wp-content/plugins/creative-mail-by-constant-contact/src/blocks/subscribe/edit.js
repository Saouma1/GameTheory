import { __ } from "@wordpress/i18n";
import {
	BlockControls,
	InspectorControls,
	RichText,
} from "@wordpress/block-editor";
import {
	SelectControl,
	TextareaControl,
	TextControl,
	Panel,
	PanelBody,
	PanelRow,
	ExternalLink,
	Dashicon,
} from "@wordpress/components";

import "./editor.scss";

export const FIELD_SETTING = {
	NOTSHOW: "notshow",
	OPTIONAL: "optional",
	REQUIRED: "required",
};

export const ON_SUBMIT_SETTING = {
	SUMMARY: "summary",
	MESSAGE: "message",
	REDIRECT: "redirect",
};

const useWPAction = (action, nonce) => {
	const [data, setData] = React.useState([]);
	const [loading, setLoading] = React.useState(false);
	const [hasLoaded, setHasLoaded] = React.useState(false);

	React.useEffect(() => {
		if (loading || hasLoaded) return;
		setLoading(true);
		jQuery
			.post(ce4wp_form_submit_data?.url, {
				action: action,
				nonce: nonce,
			})
			.done((response) => {
				setLoading(false);
				setHasLoaded(true);
				if (response?.data != null) {
					setData(response.data);
				}
			});
	}, [loading, hasLoaded, data]);

	return {
		data,
		loading,
		hasLoaded,
	};
};

const useCustomLists = () => {
	const { data, loading, hasLoaded } = useWPAction(
		"ce4wp_get_all_custom_lists",
		ce4wp_form_submit_data?.listNonce
	);

	let customLists = [];
	if (data != null && data.length != undefined) {
		customLists = data.map((list) => ({
			label: list.name,
			value: list.id,
		}));
	}
	return {
		customLists,
		loading,
		hasLoaded,
	};
};

const useIsCreativeMailActivated = () => {
	const { data, loading, hasLoaded } = useWPAction(
		"ce4wp_creative_email_activated",
		ce4wp_form_submit_data?.activatedNonce
	);
	return {
		creativeEmailIsActivated: data,
		loading,
		hasLoaded,
	};
};

export default function Edit({
	attributes,
	setAttributes,
	className,
	clientId,
}) {
	const { blockId } = attributes;
	if (!blockId) {
		setAttributes({ blockId: clientId });
	}
	const { customLists } = useCustomLists();
	const { creativeEmailIsActivated } = useIsCreativeMailActivated();
	return (
		<div className={`wp-block-ce4wp-subscribe ${className ? className : ""}`}>
			<BlockControls key="setting">
				<InspectorControls key="setting">
					<Panel header="Settings">
						<PanelBody title="Contact Segmentation" initialOpen={true}>
							<PanelRow className="no-flex">
								<fieldset>
									<i className="subTitle sub-header">
										{__(
											"Automatically assign a new contact to a list when they subscribe",
											"ce4wp"
										)}
										<br />
										<ExternalLink
											onClick={() =>
												ce4wpNavigateToDashboard(
													this,
													"fbcd9606-288a-4d82-be7c-449eaf5a3792",
													{ source: "ce4wp_form_menu" },
													ce4wpDashboardStartCallback,
													ce4wpDashboardFinishCallback
												)
											}
										>
                      <span
						  id="ce4wp-manage-lists"
						  data-link_reference="836b20fc-9ff1-41b2-912b-a8646caf05a4"
					  >
                        {__("Manage your lists", "ce4wp")}
                      </span>
										</ExternalLink>
									</i>
									<br />
									<br />
									<SelectControl
										label="list"
										value={attributes.customList}
										options={[
											{
												label: __("Don't assign to a list", "cewp4"),
												value: "",
											},
											...customLists,
										]}
										onChange={(customList) =>
											setAttributes({
												customList,
											})
										}
									/>
								</fieldset>
							</PanelRow>
						</PanelBody>
						<PanelBody title="On submission" initialOpen={true}>
							<PanelRow>
								<fieldset>
									<SelectControl
										label="On submission"
										value={attributes.onSubmissionSetting}
										options={[
											{
												label: "Show a custom text message",
												value: ON_SUBMIT_SETTING.MESSAGE,
											},
											{
												label: "Show a summary of submitted fields",
												value: ON_SUBMIT_SETTING.SUMMARY,
											},
											{
												label: "Redirect",
												value: ON_SUBMIT_SETTING.REDIRECT,
											},
										]}
										onChange={(onSubmissionSetting) =>
											setAttributes({ onSubmissionSetting })
										}
									/>
								</fieldset>
							</PanelRow>
							{attributes.onSubmissionSetting === ON_SUBMIT_SETTING.MESSAGE && (
								<PanelRow>
									<fieldset>
										<TextareaControl
											label="Message text"
											value={attributes.onSubmission}
											onChange={(onSubmission) =>
												setAttributes({ onSubmission })
											}
										/>
									</fieldset>
								</PanelRow>
							)}
							{attributes.onSubmissionSetting === ON_SUBMIT_SETTING.REDIRECT && (
								<PanelRow>
									<fieldset>
										<TextControl
											label="Redirect link"
											value={attributes.redirectLink}
											onChange={(redirectLink) =>
												setAttributes({ redirectLink })
											}
										/>
									</fieldset>
								</PanelRow>
							)}
						</PanelBody>
						<PanelBody title="Disclaimer settings" initialOpen={true}>
							<PanelRow className="no-flex">
								<fieldset>
									<SelectControl
										label="Permission to mail"
										value={attributes.emailPermission}
										options={[
											{
												label: "message",
												value: "message",
											},
											{
												label: "checkbox",
												value: "checkbox",
											},
										]}
										onChange={(emailPermission) =>
											setAttributes({
												...attributes,
												emailPermission: emailPermission,
											})
										}
									/>
								</fieldset>
							</PanelRow>
						</PanelBody>
						<PanelBody title="Field settings" initialOpen={true}>
							<PanelRow className="no-flex">
								<fieldset>
									<SelectControl
										label="First name field"
										value={attributes.displayFirstName}
										options={[
											{
												label: "Do not show",
												value: FIELD_SETTING.NOTSHOW,
											},
											{
												label: "Optional",
												value: FIELD_SETTING.OPTIONAL,
											},
											{
												label: "Required",
												value: FIELD_SETTING.REQUIRED,
											},
										]}
										onChange={(displayFirstName) =>
											setAttributes({
												displayFirstName: displayFirstName,
											})
										}
									/>
								</fieldset>
							</PanelRow>
							<PanelRow className="no-flex">
								<fieldset>
									<SelectControl
										label="Last name field"
										value={attributes.displayLastName}
										options={[
											{
												label: "Do not show",
												value: FIELD_SETTING.NOTSHOW,
											},
											{
												label: "Optional",
												value: FIELD_SETTING.OPTIONAL,
											},
											{
												label: "Required",
												value: FIELD_SETTING.REQUIRED,
											},
										]}
										onChange={(displayLastName) =>
											setAttributes({
												displayLastName: displayLastName,
											})
										}
									/>
								</fieldset>
							</PanelRow>
							<PanelRow className="no-flex">
								<fieldset>
									<SelectControl
										label="Telephone field"
										value={attributes.displayTelephone}
										options={[
											{
												label: "Do not show",
												value: FIELD_SETTING.NOTSHOW,
											},
											{
												label: "Optional",
												value: FIELD_SETTING.OPTIONAL,
											},
											{
												label: "Required",
												value: FIELD_SETTING.REQUIRED,
											},
										]}
										onChange={(displayTelephone) =>
											setAttributes({
												displayTelephone: displayTelephone,
											})
										}
									/>
								</fieldset>
							</PanelRow>
						</PanelBody>
					</Panel>
				</InspectorControls>
			</BlockControls>
			<form name="contact-form">
				<RichText
					tagName="h2"
					onChange={(title) => {
						setAttributes({ title: title });
					}}
					value={attributes.title}
				/>
				<RichText
					tagName="p"
					className="subTitle"
					onChange={(subTitle) => {
						setAttributes({ subTitle: subTitle });
					}}
					value={attributes.subTitle}
				/>
				{creativeEmailIsActivated === false && (
					<div
						className="ce4wp-inline-notification ce4wp-inline-warning ce4wp-banner-clickable"
						onClick={() =>
							ce4wpNavigateToDashboard(
								this,
								"d25f690a-217a-4d68-9c58-8693965d4673",
								{ source: "ce4wp_form_menu" },
								ce4wpDashboardStartCallback,
								ce4wpDashboardFinishCallback
							)
						}
					>
						<Dashicon className="ce4wp-inline-warning-icon" icon="warning" />
						<div className="ce4wp-inline-warning-text">
							{__(
								"Set up Creative Mail before you use this form on your website."
							)}
						</div>
						<Dashicon
							className="ce4wp-inline-warning-arrow"
							icon="arrow-right-alt2"
						/>
					</div>
				)}
				{attributes.displayFirstName !== FIELD_SETTING.NOTSHOW && (
					<div className="inputBlock">
						<RichText
							tagName="label"
							className="firstNameLabel"
							onChange={(firstNameLabel) => {
								setAttributes({ firstNameLabel: firstNameLabel });
							}}
							value={attributes.firstNameLabel}
						/>
						{attributes.displayFirstName === FIELD_SETTING.REQUIRED && (
							<p
								className="required-text subTitle"
								style={{ color: "#ee0000" }}
							>
								*
							</p>
						)}
						<input name="first_name" type="text"></input>
					</div>
				)}
				{attributes.displayLastName !== FIELD_SETTING.NOTSHOW && (
					<div className="inputBlock">
						<RichText
							tagName="label"
							className="lastNameLabel"
							onChange={(lastNameLabel) => {
								setAttributes({ lastNameLabel: lastNameLabel });
							}}
							value={attributes.lastNameLabel}
						/>
						{attributes.displayLastName === FIELD_SETTING.REQUIRED && (
							<p
								className="required-text subTitle"
								style={{ color: "#ee0000" }}
							>
								*
							</p>
						)}
						<input name="last_name" type="text"></input>
					</div>
				)}
				{attributes.displayTelephone !== FIELD_SETTING.NOTSHOW && (
					<div class="inputBlock">
						<RichText
							tagName="label"
							className="lastNameLabel"
							onChange={(telephoneLabel) => {
								setAttributes({ telephoneLabel: telephoneLabel });
							}}
							value={attributes.telephoneLabel}
						/>
						{attributes.displayTelephone === FIELD_SETTING.REQUIRED && (
							<p
								className="required-text subTitle"
								style={{ color: "#ee0000" }}
							>
								*
							</p>
						)}
						<input name="telephone" type="text"></input>
					</div>
				)}
				<div className="inputBlock">
					<RichText
						tagName="label"
						className="emailLabel"
						onChange={(emailLabel) => {
							setAttributes({ emailLabel: emailLabel });
						}}
						value={attributes.emailLabel}
					/>
					<p className="required-text subTitle" style={{ color: "#ee0000" }}>
						*
					</p>
					<input className="textwidget" name="email" type="text"></input>
				</div>
				{attributes.emailPermission == "message" && (
					<div className="disclaimer">
						<RichText
							tagName="label"
							className="disclaimer-text"
							onChange={(disclaimer) => {
								setAttributes({ disclaimer });
							}}
							value={attributes.disclaimer}
						/>
					</div>
				)}
				{attributes.emailPermission == "checkbox" && (
					<div className="disclaimer">
						<input
							type="checkbox"
							name={`consent_check_${clientId}`}
							id={`consent_check_${clientId}`}
						/>
						<RichText
							htmlFor={`consent_check_${clientId}`}
							tagName="label"
							className="consentLabel disclaimer-label"
							onChange={(consentLabel) => {
								setAttributes({ consentLabel });
							}}
							value={attributes.consentLabel}
						/>
					</div>
				)}
				<button className="wp-block-button__link submit-button" type="button">
					{__("Subscribe", "cewp4")}
				</button>
			</form>
		</div>
	);
}
