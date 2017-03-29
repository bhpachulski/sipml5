<!DOCTYPE html>

<html>
<head>
    <script src="SIPml-api.js?svn=251" type="text/javascript"> </script>

    <!-- Styles -->
    <link href="./assets/css/bootstrap.css" rel="stylesheet" />
    <style type="text/css">
        body {
            padding-top: 80px;
            padding-bottom: 40px;
        }

        .navbar-inner-red {
            background-color: #600000;
            background-image: none;
            background-repeat: no-repeat;
            filter: none;
        }

        .full-screen {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .normal-screen {
            position: relative;
        }

        .call-options {
            padding: 5px;
            background-color: #f0f0f0;
            border: 1px solid #eee;
            border: 1px solid rgba(0, 0, 0, 0.08);
            -webkit-border-radius: 4px;
            -moz-border-radius: 4px;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
            -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
            -webkit-transition-property: opacity;
            -moz-transition-property: opacity;
            -o-transition-property: opacity;
            -webkit-transition-duration: 2s;
            -moz-transition-duration: 2s;
            -o-transition-duration: 2s;
        }

        .label-align {
            display: block;
            padding-left: 15px;
            text-indent: -15px;
        }

        .input-align {
            width: 13px;
            height: 13px;
            padding: 0;
            margin: 0;
            vertical-align: bottom;
            position: relative;
            top: -1px;
            *overflow: hidden;
        }

        .glass-panel {
            z-index: 99;
            position: fixed;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            top: 0;
            left: 0;
            opacity: 0.8;
            background-color: Gray;
        }

        .div-keypad {
            z-index: 100;
            position: fixed;
            -moz-transition-property: left top;
            -o-transition-property: left top;
            -webkit-transition-duration: 2s;
            -moz-transition-duration: 2s;
            -o-transition-duration: 2s;
        }
    </style>
    <link href="./assets/css/bootstrap-responsive.css" rel="stylesheet" />

    <!-- Javascript code -->
    <script type="text/javascript">

        var sTransferNumber;
        var oRingTone, oRingbackTone;
        var oSipStack, oSipSessionRegister, oSipSessionCall, oSipSessionTransferCall;
        var audioRemote;
        var oNotifICall;
        var oConfigCall;
        var oReadyStateTimer;

        var numeroDeOrigem;
        var callId;

    // "Info"
    debugLevel = "error";

    C =
    {
        divKeyPadWidth: 220
    };

    window.onload = function () {
        window.console && window.console.info && window.console.info("location=" + window.location);

        audioRemote = document.getElementById("audio_remote");

        SIPml.setDebugLevel(debugLevel);

            // Initialize call button
            uiBtnCallSetText("Call");

            var getPVal = function (PName) {
                var query = window.location.search.substring(1);
                var vars = query.split('&');
                for (var i = 0; i < vars.length; i++) {
                    var pair = vars[i].split('=');
                    if (decodeURIComponent(pair[0]) === PName) {
                        return decodeURIComponent(pair[1]);
                    }
                }
                return null;
            }

            var preInit = function () {
                // set default webrtc type (before initialization)
                var s_webrtc_type = getPVal("wt");
                var s_fps = getPVal("fps");
                var s_mbwu = getPVal("mbwu"); // maxBandwidthUp (kbps)
                var s_mbwd = getPVal("mbwd"); // maxBandwidthUp (kbps)
                var s_za = getPVal("za"); // ZeroArtifacts
                var s_ndb = getPVal("ndb"); // NativeDebug

                if (s_webrtc_type) SIPml.setWebRtcType(s_webrtc_type);

                // initialize SIPML5
                SIPml.init(postInit);

                // set other options after initialization
                if (s_fps) SIPml.setFps(parseFloat(s_fps));
                if (s_mbwu) SIPml.setMaxBandwidthUp(parseFloat(s_mbwu));
                if (s_mbwd) SIPml.setMaxBandwidthDown(parseFloat(s_mbwd));
                if (s_za) SIPml.setZeroArtifacts(s_za === "true");
                if (s_ndb == "true") SIPml.startNativeDebug();

                //var rinningApps = SIPml.getRunningApps();
                //var _rinningApps = Base64.decode(rinningApps);
                //tsk_utils_log_info(_rinningApps);
            }

            oReadyStateTimer = setInterval(function () {
                if (document.readyState === "complete") {
                    clearInterval(oReadyStateTimer);
                    // initialize SIPML5
                    preInit();
                }
            },
            500);

        };

        function postInit() {
            // check for WebRTC support
            if (!SIPml.isWebRtcSupported()) {
                // is it chrome?
                if (SIPml.getNavigatorFriendlyName() == 'chrome') {
                    if (confirm("You're using an old Chrome version or WebRTC is not enabled.\nDo you want to see how to enable WebRTC?")) {
                        window.location = 'http://www.webrtc.org/running-the-demos';
                    }
                    else {
                        window.location = "index.html";
                    }
                    return;
                }
                else {
                    if (confirm("webrtc-everywhere extension is not installed. Do you want to install it?\nIMPORTANT: You must restart your browser after the installation.")) {
                        window.location = 'https://github.com/sarandogou/webrtc-everywhere';
                    }
                    else {
                        // Must do nothing: give the user the chance to accept the extension
                        // window.location = "index.html";
                    }
                }
            }

            // checks for WebSocket support
            if (!SIPml.isWebSocketSupported()) {
                if (confirm('Your browser don\'t support WebSockets.\nDo you want to download a WebSocket-capable browser?')) {
                    window.location = 'https://www.google.com/intl/en/chrome/browser/';
                }
                else {
                    window.location = "index.html";
                }
                return;
            }

            if (!SIPml.isWebRtcSupported()) {
                if (confirm('Your browser don\'t support WebRTC.\naudio/video calls will be disabled.\nDo you want to download a WebRTC-capable browser?')) {
                    window.location = 'https://www.google.com/intl/en/chrome/browser/';
                }
            }

            btnRegister.disabled = false;
            document.body.style.cursor = 'default';
            oConfigCall = {
                audio_remote: audioRemote,
                video_local: null,
                video_remote: null,
                screencast_window_id: 0x00000000, // entire desktop
                bandwidth: { audio: undefined, video: undefined },
                video_size: { minWidth: undefined, minHeight: undefined, maxWidth: undefined, maxHeight: undefined },
                events_listener: { events: '*', listener: onSipEventSession },
                sip_caps: [
                { name: '+g.oma.sip-im' },
                { name: 'language', value: '\"pt_BR,en\"' }
                ]
            };
        }


        function sipRegister(realmIp, privateIdentity, publicIdentity, password, displayname, webSocketProxyUrl, iceServers) {
            try {
                btnRegister.disabled = true;


                var o_impu = tsip_uri.prototype.Parse(publicIdentity);
                if (!o_impu || !o_impu.s_user_name || !o_impu.s_host) {
                    txtRegStatus.innerHTML = "<b>[" + publicIdentity + "] is not a valid Public identity</b>";
                    btnRegister.disabled = false;
                    return;
                }

                // enable notifications if not already done
                if (window.webkitNotifications && window.webkitNotifications.checkPermission() != 0) {
                    window.webkitNotifications.requestPermission();
                }

                // update debug level to be sure new values will be used if the user haven't updated the page
                //"info"
                SIPml.setDebugLevel(debugLevel);

                // create SIP stack
                oSipStack = new SIPml.Stack({
                    realm: realmIp,
                    impi: privateIdentity,
                    impu: publicIdentity,
                    password: password,
                    display_name: displayname,
                    websocket_proxy_url: webSocketProxyUrl,
                    outbound_proxy_url: null,
                    ice_servers: iceServers,
                    enable_rtcweb_breaker: false,
                    events_listener: { events: '*', listener: onSipEventStack },
                    enable_early_ims: true, // Must be true unless you're using a real IMS network
                    enable_media_stream_cache: false,
                    bandwidth: null, // could be redefined a session-level
                    video_size: null, // could be redefined a session-level
                    sip_headers: [
                    { name: 'User-Agent', value: 'IM-client/OMA1.0 sipML5-v1.2016.03.04' },
                    { name: 'Organization', value: 'Faculdade Integrado' }
                    ]});

                if (oSipStack.start() != 0) {
                    txtRegStatus.innerHTML = '<b>Failed to start the SIP stack</b>';
                } else return;

            } catch (e) {
                txtRegStatus.innerHTML = "<b>2:" + e + "</b>";
            }
            btnRegister.disabled = false;
        }

        // sends SIP REGISTER (expires=0) to logout
        function sipUnRegister() {
            if (oSipStack) {
                oSipStack.stop(); // shutdown all sessions
            }
        }

        // makes a call (SIP INVITE)
        function sipCall(s_type) {
            if (oSipStack && !oSipSessionCall && !tsk_string_is_null_or_empty(txtPhoneNumber.value)) {
                if (s_type == 'call-screenshare') {
                    if (!SIPml.isScreenShareSupported()) {
                        alert('Screen sharing not supported. Are you using chrome 26+?');
                        return;
                    }
                    if (!location.protocol.match('https')) {
                        if (confirm("Screen sharing requires https://. Do you want to be redirected?")) {
                            sipUnRegister();
                            window.location = 'https://ns313841.ovh.net/call.htm';
                        }
                        return;
                    }
                }

                btnCall.disabled = true;
                btnHangUp.disabled = false;


                oConfigCall.bandwidth = ""; // already defined at stack-level but redifined to use latest values
                oConfigCall.video_size = ""; // already defined at stack-level but redifined to use latest values


                // create call session
                oSipSessionCall = oSipStack.newSession(s_type, oConfigCall);
                // make call
                if (oSipSessionCall.call(txtPhoneNumber.value) != 0) {
                    oSipSessionCall = null;
                    txtCallStatus.value = 'Failed to make call';
                    btnCall.disabled = false;
                    btnHangUp.disabled = true;
                    return;
                }

                // saveCallOptions();

            } else if (oSipSessionCall) {
                txtCallStatus.innerHTML = '<i>Connecting...</i>';
                oSipSessionCall.accept(oConfigCall);
            }
        }

        // transfers the call
        function sipTransfer() {
            if (oSipSessionCall) {
                var s_destination = prompt('Enter destination number', '');
                if (!tsk_string_is_null_or_empty(s_destination)) {
                    btnTransfer.disabled = true;
                    if (oSipSessionCall.transfer(s_destination) != 0) {
                        txtCallStatus.innerHTML = '<i>Call transfer failed</i>';
                        btnTransfer.disabled = false;
                        return;
                    }
                    txtCallStatus.innerHTML = '<i>Transfering the call...</i>';
                }
            }
        }

        // holds or resumes the call
        function sipToggleHoldResume() {
            if (oSipSessionCall) {
                var i_ret;
                btnHoldResume.disabled = true;
                txtCallStatus.innerHTML = oSipSessionCall.bHeld ? '<i>Resuming the call...</i>' : '<i>Holding the call...</i>';
                i_ret = oSipSessionCall.bHeld ? oSipSessionCall.resume() : oSipSessionCall.hold();
                if (i_ret != 0) {
                    txtCallStatus.innerHTML = '<i>Hold / Resume failed</i>';
                    btnHoldResume.disabled = false;
                    return;
                }
            }
        }

        // Mute or Unmute the call
        function sipToggleMute() {
            if (oSipSessionCall) {
                var i_ret;
                var bMute = !oSipSessionCall.bMute;
                txtCallStatus.innerHTML = bMute ? '<i>Mute the call...</i>' : '<i>Unmute the call...</i>';
                i_ret = oSipSessionCall.mute('audio', bMute);
                if (i_ret != 0) {
                    txtCallStatus.innerHTML = '<i>Mute / Unmute failed</i>';
                    return;
                }
                oSipSessionCall.bMute = bMute;
                btnMute.value = bMute ? "Unmute" : "Mute";
            }
        }

        // terminates the call (SIP BYE or CANCEL)
        function sipHangUp() {
            if (oSipSessionCall) {
                txtCallStatus.innerHTML = '<i>Terminating the call...</i>';
                oSipSessionCall.hangup({ events_listener: { events: '*', listener: onSipEventSession } });
            }
        }

        function sipSendDTMF(c) {
            if (oSipSessionCall && c) {
                if (oSipSessionCall.dtmf(c) == 0) {
                    try { dtmfTone.play(); } catch (e) { }
                }
            }
        }

        function startRingTone() {
            try { ringtone.play(); }
            catch (e) { }
        }

        function stopRingTone() {
            try { ringtone.pause(); }
            catch (e) { }
        }

        function startRingbackTone() {
            try { ringbacktone.play(); }
            catch (e) { }
        }

        function stopRingbackTone() {
            try { ringbacktone.pause(); }
            catch (e) { }
        }

        function openKeyPad() {
            divKeyPad.style.visibility = 'visible';
            divKeyPad.style.left = ((document.body.clientWidth - C.divKeyPadWidth) >> 1) + 'px';
            divKeyPad.style.top = '70px';
            divGlassPanel.style.visibility = 'visible';
        }

        function closeKeyPad() {
            divKeyPad.style.left = '0px';
            divKeyPad.style.top = '0px';
            divKeyPad.style.visibility = 'hidden';
            divGlassPanel.style.visibility = 'hidden';
        }        

        function showNotifICall(s_number) {
            // permission already asked when we registered
            if (window.webkitNotifications && window.webkitNotifications.checkPermission() == 0) {
                if (oNotifICall) {
                    oNotifICall.cancel();
                }
                oNotifICall = window.webkitNotifications.createNotification('images/sipml-34x39.png', 'Incaming call', 'Incoming call from ' + s_number);
                oNotifICall.onclose = function () { oNotifICall = null; };
                oNotifICall.show();
            }
        }

        function uiOnConnectionEvent(b_connected, b_connecting) { // should be enum: connecting, connected, terminating, terminated
            btnRegister.disabled = b_connected || b_connecting;
            btnUnRegister.disabled = !b_connected && !b_connecting;
            btnCall.disabled = !(b_connected && tsk_utils_have_webrtc() && tsk_utils_have_stream());
            btnHangUp.disabled = !oSipSessionCall;
        }

        function uiDisableCallOptions() {
            if (window.localStorage) {
                window.localStorage.setItem('org.doubango.expert.disable_callbtn_options', 'true');
                uiBtnCallSetText('Call');
                alert('Use expert view to enable the options again (/!\\requires re-loading the page)');
            }
        }

        function uiBtnCallSetText(s_text) {
            switch (s_text) {
                case "Call": {
                    var bDisableCallBtnOptions = "true";
                    btnCall.value = btnCall.innerHTML = 'Call';
                    btnCall.setAttribute("class", "btn btn-primary");
                    btnCall.onclick = function () { sipCall('call-audio'); }
                    ulCallOptions.style.visibility = "hidden";
                    
                    break;
                }
                default: {
                    btnCall.value = btnCall.innerHTML = s_text;
                    btnCall.setAttribute("class", "btn btn-primary");
                    btnCall.onclick = function () { sipCall('call-audio'); };
                    ulCallOptions.style.visibility = "hidden";

                    break;
                }
            }

            if (ulCallOptions.parentNode == divBtnCallGroup) {
                document.body.appendChild(ulCallOptions);
            }
        }

        function uiCallTerminated(s_description) {
            uiBtnCallSetText("Call");
            btnHangUp.value = 'HangUp';
            btnHoldResume.value = 'hold';
            btnMute.value = "Mute";
            btnCall.disabled = false;
            btnHangUp.disabled = true;
            if (window.btnBFCP) window.btnBFCP.disabled = true;

            oSipSessionCall = null;

            stopRingbackTone();
            stopRingTone();

            txtCallStatus.innerHTML = "<i>" + s_description + "</i>";

            divCallOptions.style.opacity = 0;

            if (oNotifICall) {
                oNotifICall.cancel();
                oNotifICall = null;
            }

            setTimeout(function () { if (!oSipSessionCall) txtCallStatus.innerHTML = ''; }, 2500);
            
            //Registro da Call
            alert('Aqui registramos a call ...' + 'Ramal ' + numeroDeOrigem + ' CallId: ' + callId);

        }

        // Callback function for SIP Stacks
        function onSipEventStack(e /*SIPml.Stack.Event*/) {
            tsk_utils_log_info('==stack event = ' + e.type);
            switch (e.type) {
                case 'started': {
                        // catch exception for IE (DOM not ready)
                        try {
                            // LogIn (REGISTER) as soon as the stack finish starting
                            oSipSessionRegister = this.newSession('register', {
                                expires: 200,
                                events_listener: { events: '*', listener: onSipEventSession },
                                sip_caps: [
                                { name: '+g.oma.sip-im', value: null },
                                            //{ name: '+sip.ice' }, // rfc5768: FIXME doesn't work with Polycom TelePresence
                                            { name: '+audio', value: null },
                                            { name: 'language', value: '\"en,fr\"' }
                                            ]
                                        });
                            oSipSessionRegister.register();
                        } catch (e) {
                            txtRegStatus.value = txtRegStatus.innerHTML = "<b>1:" + e + "</b>";
                            btnRegister.disabled = false;
                        }
                        break;
                    }
                    case 'stopping': case 'stopped': case 'failed_to_start': case 'failed_to_stop': {
                        var bFailure = (e.type == 'failed_to_start') || (e.type == 'failed_to_stop');
                        oSipStack = null;
                        oSipSessionRegister = null;
                        oSipSessionCall = null;

                        uiOnConnectionEvent(false, false);

                        stopRingbackTone();
                        stopRingTone();

                        divCallOptions.style.opacity = 0;

                        txtCallStatus.innerHTML = '';
                        txtRegStatus.innerHTML = bFailure ? "<i>Disconnected: <b>" + e.description + "</b></i>" : "<i>Disconnected</i>";
                        break;
                    }

                    case 'i_new_call': {
                        if (oSipSessionCall) {
                            // do not accept the incoming call if we're already 'in call'
                            e.newSession.hangup(); // comment this line for multi-line support
                        } else {                            

                            oSipSessionCall = e.newSession;

                            // start listening for events
                            oSipSessionCall.setConfiguration(oConfigCall);

                            uiBtnCallSetText('Answer');
                            btnHangUp.value = 'Reject';
                            btnCall.disabled = false;
                            btnHangUp.disabled = false;

                            startRingTone();

                            var sRemoteNumber = (oSipSessionCall.getRemoteFriendlyName() || 'unknown');
                            txtCallStatus.innerHTML = "<i>Incoming call from [<b>" + sRemoteNumber + "</b>]</i>";
                            showNotifICall(sRemoteNumber);

                            //oSipSessionCall.accept(oConfigCall);

                            //Numero de origem
                            numeroDeOrigem = oSipSessionCall.o_session.o_uri_from.s_user_name;

                            //ID
                            callId = e.o_event.o_message.o_hdr_From.s_display_name;

                        }
                        break;
                    }

                    case 'm_permission_requested':
                    {
                        divGlassPanel.style.visibility = 'visible';
                        break;
                    }
                    case 'm_permission_accepted':
                    case 'm_permission_refused':
                    {
                        divGlassPanel.style.visibility = 'hidden';
                        if (e.type == 'm_permission_refused') {
                            uiCallTerminated('Media stream permission denied');
                        }
                        break;
                    }

                    case 'starting': default: break;
                }
            };

        // Callback function for SIP sessions (INVITE, REGISTER, MESSAGE...)
        function onSipEventSession(e /* SIPml.Session.Event */) {
            tsk_utils_log_info('==session event = ' + e.type);

            switch (e.type) {
                case 'connecting': case 'connected':
                {
                    var bConnected = (e.type == 'connected');
                    if (e.session == oSipSessionRegister) {
                        uiOnConnectionEvent(bConnected, !bConnected);
                        txtRegStatus.innerHTML = "<i>" + e.description + "</i>";
                    }
                    else if (e.session == oSipSessionCall) {
                        btnHangUp.value = 'HangUp';
                        btnCall.disabled = true;
                        btnHangUp.disabled = false;
                        btnTransfer.disabled = false;
                        if (window.btnBFCP) window.btnBFCP.disabled = false;

                        if (bConnected) {
                            stopRingbackTone();
                            stopRingTone();

                            if (oNotifICall) {
                                oNotifICall.cancel();
                                oNotifICall = null;
                            }
                        }

                        txtCallStatus.innerHTML = "<i>" + e.description + "</i>";
                        divCallOptions.style.opacity = bConnected ? 1 : 0;

                    }
                    break;
                    } // 'connecting' | 'connected'
                    case 'terminating': case 'terminated':
                    {

                        if (e.session == oSipSessionRegister) {
                            uiOnConnectionEvent(false, false);

                            oSipSessionCall = null;
                            oSipSessionRegister = null;

                            txtRegStatus.innerHTML = "<i>" + e.description + "</i>";
                        }
                        else if (e.session == oSipSessionCall) {
                            uiCallTerminated(e.description);
                        }
                        break;
                    } // 'terminating' | 'terminated'

                    case 'm_stream_audio_local_added':
                    case 'm_stream_audio_local_removed':
                    case 'm_stream_audio_remote_added':
                    case 'm_stream_audio_remote_removed':
                    {
                        break;
                    }

                    case 'i_ect_new_call':
                    {
                        oSipSessionTransferCall = e.session;
                        break;
                    }

                    case 'i_ao_request':
                    {
                        if (e.session == oSipSessionCall) {
                            var iSipResponseCode = e.getSipResponseCode();
                            if (iSipResponseCode == 180 || iSipResponseCode == 183) {
                                startRingbackTone();
                                txtCallStatus.innerHTML = '<i>Remote ringing...</i>';
                            }
                        }
                        break;
                    }

                    case 'm_early_media':
                    {
                        if (e.session == oSipSessionCall) {
                            stopRingbackTone();
                            stopRingTone();
                            txtCallStatus.innerHTML = '<i>Early media started</i>';
                        }
                        break;
                    }

                    case 'm_local_hold_ok':
                    {
                        if (e.session == oSipSessionCall) {
                            if (oSipSessionCall.bTransfering) {
                                oSipSessionCall.bTransfering = false;
                                // this.AVSession.TransferCall(this.transferUri);
                            }
                            btnHoldResume.value = 'Resume';
                            btnHoldResume.disabled = false;
                            txtCallStatus.innerHTML = '<i>Call placed on hold</i>';
                            oSipSessionCall.bHeld = true;
                        }
                        break;
                    }
                    case 'm_local_hold_nok':
                    {
                        if (e.session == oSipSessionCall) {
                            oSipSessionCall.bTransfering = false;
                            btnHoldResume.value = 'Hold';
                            btnHoldResume.disabled = false;
                            txtCallStatus.innerHTML = '<i>Failed to place remote party on hold</i>';
                        }
                        break;
                    }
                    case 'm_local_resume_ok':
                    {
                        if (e.session == oSipSessionCall) {
                            oSipSessionCall.bTransfering = false;
                            btnHoldResume.value = 'Hold';
                            btnHoldResume.disabled = false;
                            txtCallStatus.innerHTML = '<i>Call taken off hold</i>';
                            oSipSessionCall.bHeld = false;

                        }
                        break;
                    }
                    case 'm_local_resume_nok':
                    {
                        if (e.session == oSipSessionCall) {
                            oSipSessionCall.bTransfering = false;
                            btnHoldResume.disabled = false;
                            txtCallStatus.innerHTML = '<i>Failed to unhold call</i>';
                        }
                        break;
                    }
                    case 'm_remote_hold':
                    {
                        if (e.session == oSipSessionCall) {
                            txtCallStatus.innerHTML = '<i>Placed on hold by remote party</i>';
                        }
                        break;
                    }
                    case 'm_remote_resume':
                    {
                        if (e.session == oSipSessionCall) {
                            txtCallStatus.innerHTML = '<i>Taken off hold by remote party</i>';
                        }
                        break;
                    }
                    case 'm_bfcp_info':
                    {
                        if (e.session == oSipSessionCall) {
                            txtCallStatus.innerHTML = 'BFCP Info: <i>' + e.description + '</i>';
                        }
                        break;
                    }

                    case 'o_ect_trying':
                    {
                        if (e.session == oSipSessionCall) {
                            txtCallStatus.innerHTML = '<i>Call transfer in progress...</i>';
                        }
                        break;
                    }
                    case 'o_ect_accepted':
                    {
                        if (e.session == oSipSessionCall) {
                            txtCallStatus.innerHTML = '<i>Call transfer accepted</i>';
                        }
                        break;
                    }
                    case 'o_ect_completed':
                    case 'i_ect_completed':
                    {
                        if (e.session == oSipSessionCall) {
                            txtCallStatus.innerHTML = '<i>Call transfer completed</i>';
                            btnTransfer.disabled = false;
                            if (oSipSessionTransferCall) {
                                oSipSessionCall = oSipSessionTransferCall;
                            }
                            oSipSessionTransferCall = null;
                        }
                        break;
                    }
                    case 'o_ect_failed':
                    case 'i_ect_failed':
                    {
                        if (e.session == oSipSessionCall) {
                            txtCallStatus.innerHTML = '<i>Call transfer failed</i>';
                            btnTransfer.disabled = false;
                        }
                        break;
                    }
                    case 'o_ect_notify':
                    case 'i_ect_notify':
                    {
                        if (e.session == oSipSessionCall) {
                            txtCallStatus.innerHTML = "<i>Call Transfer: <b>" + e.getSipResponseCode() + " " + e.description + "</b></i>";
                            if (e.getSipResponseCode() >= 300) {
                                if (oSipSessionCall.bHeld) {
                                    oSipSessionCall.resume();
                                }
                                btnTransfer.disabled = false;
                            }
                        }
                        break;
                    }
                    case 'i_ect_requested':
                    {
                        if (e.session == oSipSessionCall) {
                            var s_message = "Do you accept call transfer to [" + e.getTransferDestinationFriendlyName() + "]?";//FIXME
                            if (confirm(s_message)) {
                                txtCallStatus.innerHTML = "<i>Call transfer in progress...</i>";
                                oSipSessionCall.acceptTransfer();
                                break;
                            }
                            oSipSessionCall.rejectTransfer();
                        }
                        break;
                    }
                }
            }

        </script>
    </head>
    <body>        
        <div class="container">
            <div class="row-fluid">
                <div class="span4 well">

                    <label style="width: 100%;" align="center" id="txtRegStatus">
                    </label>
                    <h2>
                        Registration
                    </h2>
                    <br />
                    
                    <input type="button" class="btn btn-success" id="btnRegister" value="LogIn" disabled onclick="sipRegister('10.1.0.107', '6002', 'sip:6002@10.1.0.107', '123456', 'Webcall', 'ws://10.1.0.107:8088/ws', '[]');" />

                    <input type="button" class="btn btn-danger" id="btnUnRegister" value="LogOut" disabled onclick='sipUnRegister();' />


                </div>
                <div id="divCallCtrl" class="span7 well" style='display:table-cell; vertical-align:middle'>
                    <label style="width: 100%;" align="center" id="txtCallStatus">
                    </label>
                    <h2>
                        Call control
                    </h2>
                    <br />
                    <table style='width: 100%;'>
                        <tr>
                            <td style="white-space:nowrap;">
                                <input type="text" style="width: 100%; height:100%;" id="txtPhoneNumber" value="" placeholder="Enter phone number to call" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="1" align="right">
                                <div class="btn-toolbar" style="margin: 0; vertical-align:middle">
                                    <div id="divBtnCallGroup" class="btn-group">
                                        <button id="btnCall" disabled class="btn btn-primary" data-toggle="dropdown">Call</button>
                                    </div>&nbsp;&nbsp;
                                    <div class="btn-group">
                                        <input type="button" id="btnHangUp" style="margin: 0; vertical-align:middle; height: 100%;" class="btn btn-primary" value="HangUp" onclick='sipHangUp();' disabled />
                                    </div>
                                </div>
                            </td>
                        </tr>                    
                        <tr>
                            <td align='center'>
                                <div id='divCallOptions' class='call-options' style='opacity: 0; margin-top: 0px'>
                                    <input type="button" class="btn" style="" id="btnMute" value="Mute" onclick='sipToggleMute();' /> &nbsp;
                                    <input type="button" class="btn" style="" id="btnHoldResume" value="Hold" onclick='sipToggleHoldResume();' /> &nbsp;
                                    <input type="button" class="btn" style="" id="btnTransfer" value="Transfer" onclick='sipTransfer();' /> &nbsp;
                                    <input type="button" class="btn" style="" id="btnKeyPad" value="KeyPad" onclick='openKeyPad();' />
                                </div>
                            </td>
                        </tr>
                    </table>            

                </div>
            </div>
        </div>


        <div id='divGlassPanel' class='glass-panel' style='visibility:hidden'></div>

        <div id='divKeyPad' class='span2 well div-keypad' style="left:0px; top:0px; width:250; height:240; visibility:hidden">
            <table style="width: 100%; height: 100%">
                <tr><td><input type="button" style="width: 33%" class="btn" value="1" onclick="sipSendDTMF('1');" /><input type="button" style="width: 33%" class="btn" value="2" onclick="sipSendDTMF('2');" /><input type="button" style="width: 33%" class="btn" value="3" onclick="sipSendDTMF('3');" /></td></tr>
                <tr><td><input type="button" style="width: 33%" class="btn" value="4" onclick="sipSendDTMF('4');" /><input type="button" style="width: 33%" class="btn" value="5" onclick="sipSendDTMF('5');" /><input type="button" style="width: 33%" class="btn" value="6" onclick="sipSendDTMF('6');" /></td></tr>
                <tr><td><input type="button" style="width: 33%" class="btn" value="7" onclick="sipSendDTMF('7');" /><input type="button" style="width: 33%" class="btn" value="8" onclick="sipSendDTMF('8');" /><input type="button" style="width: 33%" class="btn" value="9" onclick="sipSendDTMF('9');" /></td></tr>
                <tr><td><input type="button" style="width: 33%" class="btn" value="*" onclick="sipSendDTMF('*');" /><input type="button" style="width: 33%" class="btn" value="0" onclick="sipSendDTMF('0');" /><input type="button" style="width: 33%" class="btn" value="#" onclick="sipSendDTMF('#');" /></td></tr>
                <tr><td colspan=3><input type="button" style="width: 100%" class="btn btn-medium btn-danger" value="close" onclick="closeKeyPad();" /></td></tr>
            </table>
        </div>

        <!-- Call button options -->
        <ul id="ulCallOptions" class="dropdown-menu" style="visibility:hidden">
            <li><a href="#" onclick='sipCall("call-audio");'>Audio</a></li>
            <li class="divider"></li>
            <li><a href="#" onclick='uiDisableCallOptions();'><b>Disable these options</b></a></li>
        </ul>

        <script type="text/javascript" src="./assets/js/jquery.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-transition.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-alert.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-modal.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-dropdown.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-scrollspy.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-tab.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-tooltip.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-popover.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-button.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-collapse.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-carousel.js"></script>
        <script type="text/javascript" src="./assets/js/bootstrap-typeahead.js"></script>

        <!-- Audios -->
        <audio id="audio_remote" autoplay="autoplay"> </audio>
        <audio id="ringtone" loop src="sounds/ringtone.wav"> </audio>
        <audio id="ringbacktone" loop src="sounds/ringbacktone.wav"> </audio>
        <audio id="dtmfTone" src="sounds/dtmf.wav"> </audio>

    </body>
    </html>
