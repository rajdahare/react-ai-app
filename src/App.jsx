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
  const [isStreaming, setIsStreaming] = useState(false);

  function updateLastMessageContent(content) {
    setMessages( (prevMessages) => 
      prevMessages.map((messages, index) => 
        index === prevMessages.length - 1 
        ? {...messages, content: `${messages.content}${content}`} 
        : messages
      )
    );
  }

  function addMessage(message) {
    setMessages( (prevMessages) => [...prevMessages, message]);
  }

  async function handleContendSend(content) {
    addMessage({role: "user", content});
    setIsLoading(true);
    try {
      const result = await assistant.chatStream(content);
      // addMessage({role: "assistant", content: result });
      
      let isFirstChunk = false;
      for await (const chunk of result) {
        if (!isFirstChunk) {
          isFirstChunk = true;
          addMessage({role: "assistant", content: "" });
          setIsLoading(false);
          setIsStreaming(true);
        }
        updateLastMessageContent(chunk);
      }
      setIsStreaming(false);
    } catch (error) {
      addMessage({role: "system", content: "Sorry I couldn't process your request - " + error.message});
      setIsLoading(false);
      setIsStreaming(false);
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
    <Controls isDisabled={isLoading || isStreaming} onSend={handleContendSend}/> 
    </div>
  );
}


export default App
