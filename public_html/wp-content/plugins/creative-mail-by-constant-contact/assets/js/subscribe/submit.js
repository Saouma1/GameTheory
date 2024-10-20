// phpcs:disable
/**
 * addOnSubmit.
 * Adds the submit function to subscribe contact forms
 * @package CreativeMail
 */
function setSummaryText(parentElement, name, text) {
  if (!text) return;
  const element = parentElement.getElementsByClassName(name)[0];
  if (element) element.textContent = text;
}

function setSummary(parentElement, values) {
  for (const [key, value] of Object.entries(values)) {
    setSummaryText(parentElement, key, value);
  }
}

(function addOnSubmit() {
  let submitButtons = document.getElementsByClassName("cm-contact-form");
  for (let element of submitButtons) {
    element.onsubmit = (e) => {
      e.preventDefault();
      let formElement = e.target;
      let firstName = formElement.getElementsByClassName("firstName")[0]?.value;
      let lastName = formElement.getElementsByClassName("lastName")[0]?.value;
      let email = formElement.getElementsByClassName("email")[0]?.value;
      let telephone = formElement.getElementsByClassName("telephone")[0]?.value;
      let consent = formElement.getElementsByClassName("consent_check")[0]
        ?.checked;
      let listId =
        formElement.getElementsByClassName("list_id")[0]?.value || null;
      jQuery
        .post(ce4wp_form_submit_data?.url, {
          action: "ce4wp_form_submission",
          nonce: ce4wp_form_submit_data?.nonce,
          first_name: firstName,
          last_name: lastName,
          email: email,
          telephone: telephone,
          consent: consent,
          list_id: listId,
        })
        .done(function() {
          let parentElement = formElement.parentElement;
          const onSubmissionSetting = parentElement.getElementsByClassName(
            'onSubmissionSetting'
          )[0]?.value;

          if (onSubmissionSetting === "redirect") {
            var pattern = new RegExp('^(https?)://');
            let redirectValue = parentElement.getElementsByClassName(
                "redirect"
            )[0]?.value;
            if(!pattern.test(redirectValue)) {
              redirectValue = "https://" + redirectValue;
            }
            window.location.href = redirectValue;
          } else {
            formElement.style.visibility = "hidden"; 
            if (onSubmissionSetting === "summary") {
              setSummary(parentElement, {
                firstNameSummary: firstName ? firstName + " ": null,
                lastNameSummary: lastName,
                telephoneSummary: telephone,
                emailSummary: email,
              });
            }
            let onSubmission = parentElement.getElementsByClassName(
              "onSubmission"
            )[0];
            onSubmission.style.display = "block";
          }
        });
    };
  }
})();
