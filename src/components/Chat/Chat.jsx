import { useEffect, useRef } from 'react';
import Markdown from 'react-markdown';
import styles from './Chat.module.css';

const WELCOME_MESSAGE = {
  role: "assistant",
  content: "Hello! How can I assist you today?",
};

export function Chat({messages}) {
  const messageEndRef = useRef(null);
  // Scroll to the bottom when messages change
  useEffect(() => {
    const lastMessage = messages[messages.length - 1];
    if(lastMessage?.role === "user"){
        messageEndRef.current?.scrollIntoView({ behavior: "smooth" });
    }
  }, [messages]);

  return (
    <div className={styles.Chat}>
      <div className="chat-header">
        <h2>Chat with AI</h2>
      </div>
      {[WELCOME_MESSAGE, ...messages].map(({role, content}, index) =>(
        <div className={styles.Message} key={index} data-role={role}>
          <Markdown>{content}</Markdown>
        </div>
      ))}
      <div ref={messageEndRef} /> 
    </div>
  );
}