import { __ } from "@wordpress/i18n";
import { RichText } from "@wordpress/block-editor";
import { FIELD_SETTING } from "../edit";
// phpcs:disable
/**
 * addOnSubmit.
 * Adds the submit function to subscribe contact forms
 * @package CreativeMail
 */
export function deprecated_4_0({ attributes, className }) {
  return (
    <div className={`wp-block-ce4wp-subscribe ${className ? className : ''}`}>
      <div className="onSubmission" style={{ display: 'none' }}>
        <RichText.Content
          className="title"
          tagName="h2"
          value={attributes.title}
        />
        <p className="subTitle">{attributes.onSubmission}</p>
      </div>
      <form className="cm-contact-form" name="contact-form">
        <input
          className="list_id"
          name="list_id"
          type="hidden"
          value={attributes.customList}
        />
        <RichText.Content
          className="title"
          tagName="h2"
          value={attributes.title}
        />{" "}
        <RichText.Content
          className="subTitle"
          tagName="p"
          value={attributes.subTitle}
        />
        {attributes.displayFirstName !== FIELD_SETTING.NOTSHOW && (
          <div className="inputBlock">
            <RichText.Content
              tagName="label"
              value={attributes.firstNameLabel}
            />
            {attributes.displayFirstName === FIELD_SETTING.REQUIRED && (
              <span
                className="wp-caption-text required-text"
                style={{ color: "#ee0000" }}
              >
                *
              </span>
            )}
            <input
              className="firstName"
              name="first_name"
              type="text"
              required={attributes.displayFirstName === FIELD_SETTING.REQUIRED}
            />
          </div>
        )}
        {attributes.displayLastName !== FIELD_SETTING.NOTSHOW && (
          <div className="inputBlock">
            <RichText.Content
              tagName="label"
              value={attributes.lastNameLabel}
            />
            {attributes.displayLastName === FIELD_SETTING.REQUIRED && (
              <span
                className="wp-caption-text required-text"
                style={{ color: "#ee0000" }}
              >
                *
              </span>
            )}
            <input
              className="lastName"
              name="last_name"
              type="text"
              required={attributes.displayFirstName === FIELD_SETTING.REQUIRED}
            ></input>
          </div>
        )}
        {attributes.displayTelephone !== FIELD_SETTING.NOTSHOW && (
          <div className="inputBlock">
            <RichText.Content
              tagName="label"
              value={attributes.telephoneLabel}
            />
            {attributes.displayTelephone === FIELD_SETTING.REQUIRED && (
              <span
                className="wp-caption-text required-text"
                style={{ color: "#ee0000" }}
              >
                *
              </span>
            )}
            <input
              className="telephone"
              name="telephone"
              type="tel"
              pattern="[+]?[0-9\(\)\s+-]{5,20}"
              oninvalid={`setCustomValidity('${__('Please enter a valid phone number', 
                                                'creative-mail-by-constant-contact')}')`}
              oninput="setCustomValidity('')"
              required={attributes.displayFirstName === FIELD_SETTING.REQUIRED}
            ></input>
          </div>
        )}
        <div className="inputBlock">
          <RichText.Content tagName="label" value={attributes.emailLabel} />
          <span
            className="wp-caption-text required-text"
            style={{ color: "#ee0000" }}
          >
            *
                    </span>
          <input
            className="email"
            name="email"
            type="email"
            oninvalid={`setCustomValidity('${__( 'Please enter a valid email address',
                                              'creative-mail-by-constant-contact')}')`}
            oninput="setCustomValidity('')"
            required
          ></input>
        </div>
        {attributes.emailPermission == "message" && (
          <span className="disclaimer">
            <input
              className="consent_check"
              name="consent"
              type="hidden"
              checked
            ></input>
            {__(
              'By submitting your information, you are granting us permission to email you. You may unsubscribe at any time.',
              'creative-mail-by-constant-contact'
            )}
          </span>
        )}
        {attributes.emailPermission == "checkbox" && (
          <span>
            <input
              className="consent_check"
              name={`consent_check_${attributes.blockId}`}
              id={`consent_check_${attributes.blockId}`}
              type="checkbox"
            />
            <label
              htmlFor={`consent_check_${attributes.blockId}`}
              className="disclaimer"
            >
              {__('Can we send you an email from time to time?', 'creative-mail-by-constant-contact')}
            </label>
          </span>
        )}
        <button className="wp-block-button__link submit-button" type="submit">
          {__('Subscribe', 'creative-mail-by-constant-contact')}
        </button>
      </form>
    </div>
  );
}
