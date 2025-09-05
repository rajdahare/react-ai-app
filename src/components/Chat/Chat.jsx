import Markdown from 'react-markdown';
import styles from './Chat.module.css';

const WELCOME_MESSAGE = {
  role: "assistant",
  content: "Hello! How can I assist you today?",
};

export function Chat({messages}) {
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
    </div>
  );
}