export function main({ audioElement, bufferingMessageElement, bufferedAmountElement }) {
  const restartAudio = () => {
    audioElement.load();
    audioElement.play();
  };

  const showBufferingMessage = () => {
    bufferingMessageElement.style.display = 'block';
  };

  const hideBufferingMessage = () => {
    bufferingMessageElement.style.display = 'none';
  };

  const updateBufferedAmount = () => {
    const { buffered, currentTime } = audioElement;
    const bufferedAmount = buffered.length > 0 && currentTime > 0
      ? buffered.end(buffered.length - 1) - currentTime
      : 0;

    bufferedAmountElement.textContent = `Buffered: ${bufferedAmount} seconds`;
  };

  window.setInterval(updateBufferedAmount, 1000);

  return {
    restartAudio,
    showBufferingMessage,
    hideBufferingMessage
  }
}
