/* Source and licensing information for the line(s) below can be found at http://hds.harvard.edu/profiles/openscholar/modules/os/modules/os_ga/os_ga.js. */
(function($){$(document).ready(function(){var isInternal=new RegExp("^(https?):\/\/"+window.location.host,"i");$(document.body).click(function(event){$(event.target).closest("a,area").each(function(){var os_ga=Drupal.settings.os_ga,isDownload=new RegExp("\\.("+os_ga.trackDownloadExtensions+")$","i");if(isInternal.test(this.href)){if(os_ga.trackDownload&&isDownload.test(this.href)){var extension=isDownload.exec(this.href);_gaq.push(["_trackEvent","Downloads",extension[1].toUpperCase(),this.href.replace(isInternal,'')])}}else if(os_ga.trackMailto&&$(this).is("a[href^='mailto:'],area[href^='mailto:']")){_gaq.push(["_trackEvent","Mails","Click",this.href.substring(7)])}else if(os_ga.trackOutbound&&this.href.match(/^\w+:\/\//i))_gaq.push(["_trackEvent","Outbound links","Click",this.href]);if(os_ga.trackNavigation){if($(this).closest('#block-os-secondary-menu').length)var navType="Secondary Nav";if($(this).closest('#block-os-primary-menu').length)var navType="Primary Nav";if(navType)_gaq.push(["_trackEvent",navType,"Click",this.href])}})})})})(jQuery);;
/* Source and licensing information for the above line(s) can be found at http://hds.harvard.edu/profiles/openscholar/modules/os/modules/os_ga/os_ga.js. */
/* Source and licensing information for the line(s) below can be found at http://hds.harvard.edu/profiles/openscholar/modules/os/theme/os_dismiss.js. */
(function($){Drupal.behaviors.dismiss={attach:function(context){$('.messages').each(function(){$(this).prepend('<a class="dismiss">X</a>')});$('.dismiss').click(function(){$(this).parent().hide('fast')})}}})(jQuery);;
/* Source and licensing information for the above line(s) can be found at http://hds.harvard.edu/profiles/openscholar/modules/os/theme/os_dismiss.js. */