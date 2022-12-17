import './App.css';
import {useEffect, useRef, useState} from "react";

const CURRENT_TRACK_URL = (channelId) => `/radio-manager/api/pub/v0/streams/${channelId}/current-track`

function useCurrentTrack({ channelId }) {
    const [currentTrack, setCurrentTrack] = useState(null);

    useEffect(() => {

        let timeoutRef = null

        const fetchCurrentTrack = async () => {
            if (timeoutRef) {
                clearTimeout(timeoutRef)
            }

            try {
                const response = await fetch(CURRENT_TRACK_URL(channelId));
                const body = await response.json();
                const nextFetchIn = Math.min(body.data.duration - body.data.position, 5000);

                timeoutRef = setTimeout(fetchCurrentTrack, nextFetchIn);

                setCurrentTrack(body.data)
            } catch (error) {
                timeoutRef = setTimeout(fetchCurrentTrack, 5000);
            }
        }

        fetchCurrentTrack();

        return () => {
            if (timeoutRef) {
                clearTimeout(timeoutRef)
            }
        }
    }, [channelId, setCurrentTrack]);

    return currentTrack;
}

function App({ channelId }) {
    const currentTrack = useCurrentTrack({ channelId })

  return (
    <div className="App">
      <div className="Overlay">
          <div className="Overlay-cover">
              <img alt="cover" src="https://fakeimg.pl/150x150/" />
          </div>
          <div className="Overlay-track">
              <div className="Overlay-track_title">
                  {currentTrack?.title}
              </div>
              <div className="Overlay-track_artist">
                  {currentTrack?.artist}
              </div>
          </div>
      </div>
    </div>
  );
}

export default App;
