import { useState } from 'react';
import TextareaAutosize from 'react-textarea-autosize';
import styles from './Controls.module.css'

export function Controls({onSend}) {

  const [content, setContent] = useState("");

  // Handle content change in textarea
  function handleContentChange(event) {
    setContent(event.target.value);
  }

  // send content message using button click or enter key
  function handleContentSend() {
    if(content.length > 0) {
      onSend(content);
      setContent("");
    }
  }

  // send content message using enter key
  function handleEnterKeyPress(event) {
    if(event.key === "Enter" && !event.shiftKey) {
      event.preventDefault();
      handleContentSend();
    }
  }
  

  return (
    <div className={styles.Controls}>
        <div className={styles.TextAreaContainer}>
            <TextareaAutosize 
              className={styles.TextArea} 
              placeholder="Message AI Chat"
              value={content}
              minRows={1}
              maxRows={6}
              onChange={handleContentChange}
              onKeyDown={handleEnterKeyPress}
            />
        </div>
        <button 
          className={styles.Button}
          onClick={handleContentSend}
        >
          <SendIcon />
        </button>
    </div>
  );
}

function SendIcon() {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      height="24px"
      width="24px"
      viewBox="0 0 24 24"
      fill="#5f6368"
    >
      {/* Paper plane send icon */}
      <path d="M2 21l21-9L2 3v7l15 2-15 2v7z" />
    </svg>
  );
}