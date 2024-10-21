import { registerBlockType } from "@wordpress/blocks";
import "./style.scss";
import Edit from "./edit";
import save from "./save";
import { __ } from "@wordpress/i18n";
import { deprecated_4_0 }  from "./deprecated/deprecated_save";

// phpcs:disable
/**
 * Subscribe block.
 * Adds the gutenberg block subscibe so that users can add a contactform block
 * for creative mail
 * @package CreativeMail
 */
registerBlockType("ce4wp/subscribe", {
  /**
   * @see ./edit.js
   */
  edit: Edit,

  /**
   * @see ./save.js
   */
  save,
  supports: {
    // Remove the support for wide alignment.
    alignWide: false,
  },
  keywords: [ __('contact', 'creative-mail-by-constant-contact'),
              __('form', 'creative-mail-by-constant-contact'),
              __('email', 'creative-mail-by-constant-contact'),
              __('mail', 'creative-mail-by-constant-contact')],
  deprecated: [
    {
      attributes: {
        blockId: {
          type: "string",
        },
        title: {
          type: "string",
          default: "Subscribe",
        },
        subTitle: {
          type: "string",
          default: "Sign up for our newsletter and stay up to date",
        },
        firstNameLabel: {
          type: "string",
          default: "First name",
        },
        lastNameLabel: {
          type: "string",
          default: "Last name",
        },
        emailLabel: {
          type: "string",
          default: "Email",
        },
        telephoneLabel: {
          type: "string",
          default: "Telephone",
        },
        emailPermission: {
          type: "string",
          default: "checkbox",
        },
        displayTelephone: {
          type: "string",
          default: "notshow",
        },
        displayFirstName: {
          type: "string",
          default: "optional",
        },
        displayLastName: {
          type: "string",
          default: "optional",
        },
        onSubmission: {
          type: "string",
          selector: "div",
          default: "Thank you for subscribing!",
        },
        customList: {
          type: "string",
          default: null,
        },
      },
      save: deprecated_4_0,
    },
  ],
});
