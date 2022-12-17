import React from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
import App from './App';
import reportWebVitals from './reportWebVitals';

const STREAM_OVERLAY_ROUTE_REGEXP = /^\/stream-overlay\/(\d+)$/;

const element = document.getElementById('root');
const route = new URL(window.location.href);

if (STREAM_OVERLAY_ROUTE_REGEXP.test(route.pathname)) {
    let [, channelId] = STREAM_OVERLAY_ROUTE_REGEXP.exec(route.pathname);
    channelId = parseInt(channelId, 10);

    const root = ReactDOM.createRoot(element);

    root.render(
        <React.StrictMode>
            <App channelId={channelId} />
        </React.StrictMode>
    );
} else {
    // @todo 404 Not Found view
    element.innerText = '404';
}

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();
