import { useState } from 'react';
import { Assistant } from './assistants/googleai';
// import { Assistant } from './assistants/openai';
import { Loader } from './components/Loader/Loader';
import { Chat } from './components/Chat/Chat'
import { Controls } from './components/Controls/Controls'
import styles from './App.module.css'




function App() {

  const assistant = new Assistant();
  const [messages, setMessages] = useState([]);
  const [isLoading, setIsLoading] = useState(false);

  function addMessage(message) {
    setMessages( (prevMessages) => [...prevMessages, message]);
  }

  async function handleContendSend(content) {
    addMessage({role: "user", content});
    setIsLoading(true);
    try {
      const result = await assistant.chat(content);
      addMessage({role: "assistant", content: result });
    } catch (error) {
      addMessage({role: "system", content: "Error: " + error.message});
    } finally {
      setIsLoading(false);
    }
  }

  return (
    <div className={styles.App}> 
    { isLoading && <Loader /> }
    <header className={styles.Header}>
      <img className={styles.logo} src="/chat-bot.jpg" alt="logo"/>
      <h2 className={styles.Title}>AI Chatbot</h2>
    </header>
    <div className={styles.ChatContainer}>
      <Chat messages={messages} setMessages={setMessages} />
    </div>
    <Controls isDisabled={isLoading} onSend={handleContendSend}/> 
    </div>
  );
}


export default App
