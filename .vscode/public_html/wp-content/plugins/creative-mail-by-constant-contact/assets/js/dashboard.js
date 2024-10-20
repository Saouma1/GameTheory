// phpcs:disable

let html = `<!DOCTYPE html><html><head><title>Loading...</title><style>body{background-color: #F6F6F6;}.ce4wp-wrapper{position: absolute; top: 0; bottom: 0; left: 0; right: 0; padding: 5%;}.ce4w-ploader{position: relative; margin: 0 auto; width: 100px;}.ce4w-ploader:before{content: ''; display: block; padding-top: 100%;}.ce4wp-circular{animation: rotate 2s linear infinite; height: 100%; transform-origin: center center; width: 100%; position: absolute; top: 0; bottom: 0; left: 0; right: 0; margin: auto;}.path{stroke-dasharray: 1, 200; stroke-dashoffset: 0; stroke: rgb(122, 76, 168); animation: dash 1.5s ease-in-out infinite; stroke-linecap: round;}@keyframes rotate{100%{transform: rotate(360deg);}}@keyframes dash{0%{stroke-dasharray: 1, 200; stroke-dashoffset: 0;}50%{stroke-dasharray: 89, 200; stroke-dashoffset: -35px;}100%{stroke-dasharray: 89, 200; stroke-dashoffset: -124px;}}</style> </head><body> <div class="ce4wp-wrapper"> <div class="ce4w-ploader"> <svg class="ce4wp-circular" viewBox="25 25 50 50"> <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/> </svg> </div></div></body></html>`

function ce4wpNavigateToDashboard(element, linkReference, linkParameters, startCallback, finishCallback) {
  if (typeof startCallback === 'function') {
    startCallback(element)
  }

  let ce4wpWindow = window.open("about:blank", '_blank');
  ce4wpWindow.document.body.innerHTML = html;

  jQuery.ajax({
    type   : "POST",
    url    : ce4wp_data.url,
    data   : {
      nonce: ce4wp_data.nonce,
      link_reference: linkReference || undefined,
      link_parameters: linkParameters || undefined,
      action: 'ce4wp_request_sso'
    },
    success: function(response) {
      if (response.success) {
        ce4wpWindow.location = response.data.url;
        if (typeof finishCallback === 'function') {
          finishCallback(element)
        }
      }
    },
    error: function(){
      ce4wpWindow.close();
    }
  });
}

function ce4wpDashboardStartCallback (element) {
  let skeleton = document.getElementById('ce4wpskeleton')
  let loaded = document.getElementById('ce4wploaded')
  if (skeleton && loaded) {
    skeleton.style.display = "block";
    loaded.style.display = "none";
  }
}
function ce4wpDashboardFinishCallback (element) {
  let skeleton = document.getElementById('ce4wpskeleton')
  let loaded = document.getElementById('ce4wploaded')
  if (skeleton && loaded) {
    skeleton.style.display = "none";
    loaded.style.display = "block";
  }
}

function ce4wpWidgetStartCallback (element) {
  if (element) {
    element.setAttribute('disabled', true)
  }
}
function ce4wpWidgetFinishCallback (element) {
  if (element) {
    element.removeAttribute('disabled')
  }
}

function ce4wpOnMenuClick(event) {
  event.stopImmediatePropagation();
  event.preventDefault();
  let element = this;
  jQuery(function($){
    let link_reference = $(element).find("span").data("link_reference");
    ce4wpNavigateToDashboard(element, link_reference, { source: 'ce4wp_admin_menu' }, ce4wpDashboardStartCallback, ce4wpDashboardFinishCallback);
  });
}

jQuery(function($){
  $('#ce4wp-menu-dashboard').parent().on('click', ce4wpOnMenuClick);
  $('#ce4wp-menu-campaigns').parent().on('click', ce4wpOnMenuClick);
  $('#ce4wp-menu-campaigns-ideas').parent().on('click', ce4wpOnMenuClick);
  $('#ce4wp-menu-contacts').parent().on('click', ce4wpOnMenuClick);
  $('#ce4wp-menu-marketing-calendar').parent().on('click', ce4wpOnMenuClick);
  $('#ce4wp-menu-woocommerce').parent().on('click', ce4wpOnMenuClick);
  $('#ce4wp-menu-automation').parent().on('click', ce4wpOnMenuClick);
  $('#ce4wp-menu-logo-builder').parent().on('click', ce4wpOnMenuClick);
  $('#ce4wp-menu-social-booster').parent().on('click', ce4wpOnMenuClick);
  $('#ce4wp-menu-email-settings').parent().on('click', ce4wpOnMenuClick);
  $('#ce4wp-menu-settings').parent().on('click', ce4wpOnMenuClick);
});
