import React, { useState, useRef, useEffect } from 'react';
import { streamChat } from '../services/geminiService';

interface Message {
  role: 'user' | 'model';
  text: string;
}

const ChatInterface: React.FC = () => {
  const [messages, setMessages] = useState<Message[]>([
    { role: 'model', text: 'Selam Şafak Esnafım! Tezgahta durumlar nasıl? Dükkan yönetimi, yeni ürünler veya müşteriler hakkında bir sorun varsa buradayım.' }
  ]);
  const [input, setInput] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  useEffect(scrollToBottom, [messages, isTyping]);

  const handleSend = async (textToSend?: string) => {
    const messageText = textToSend || input;
    if (!messageText.trim() || isTyping) return;

    const userMessage: Message = { role: 'user', text: messageText };
    setMessages(prev => [...prev, userMessage]);
    setInput('');
    setIsTyping(true);

    try {
      const history = [...messages, userMessage].map(m => ({
        role: m.role,
        parts: [{ text: m.text }]
      }));

      let assistantText = '';
      setMessages(prev => [...prev, { role: 'model', text: '' }]);

      await streamChat(history, (chunk) => {
        assistantText += chunk;
        setMessages(prev => {
          const newMessages = [...prev];
          newMessages[newMessages.length - 1].text = assistantText;
          return newMessages;
        });
      });
    } catch (error) {
      console.error("Chat error:", error);
      setMessages(prev => [...prev, { role: 'model', text: 'Kusura bakma esnafım, bir an dalmışım. Ne diyorduk? (Bir hata oluştu)' }]);
    } finally {
      setIsTyping(false);
    }
  };

  const starters = [
    { label: "Yeni ürün önerisi", icon: "🥜" },
    { label: "Müşteri çekme fikirleri", icon: "🏘️" },
    { label: "Dükkan düzeni", icon: "🧺" },
    { label: "Bayram hazırlığı", icon: "🌙" }
  ];

  return (
    <div className="bg-white rounded-xl shadow-md overflow-hidden flex flex-col h-[600px] border border-brand-100">
      {/* Header */}
      <div className="bg-brand-50 p-4 border-b border-brand-100 flex items-center gap-3">
        <div className="w-10 h-10 bg-brand-600 rounded-full flex items-center justify-center text-xl shadow-inner border-2 border-white">
          🤖
        </div>
        <div>
          <h3 className="font-bold text-brand-900 leading-tight">Akıllı Asistan</h3>
          <div className="flex items-center gap-1.5">
            <span className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            <span className="text-[10px] text-green-700 font-bold uppercase tracking-wider">Çevrimiçi</span>
          </div>
        </div>
      </div>

      {/* Messages */}
      <div className="flex-grow overflow-y-auto p-4 space-y-4 bg-gray-50/50">
        {messages.map((msg, idx) => (
          <div 
            key={idx} 
            className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'} animate-fade-in`}
          >
            <div 
              className={`max-w-[85%] px-4 py-2.5 rounded-2xl shadow-sm text-sm leading-relaxed
                ${msg.role === 'user' 
                  ? 'bg-brand-600 text-white rounded-tr-none' 
                  : 'bg-white text-brand-950 border border-brand-100 rounded-tl-none'}`}
            >
              {msg.text || (isTyping && idx === messages.length - 1 ? "..." : "")}
            </div>
          </div>
        ))}
        <div ref={messagesEndRef} />
      </div>

      {/* Quick Starters */}
      {messages.length < 3 && (
        <div className="p-3 bg-white border-t border-brand-50 overflow-x-auto no-scrollbar">
            <div className="flex gap-2 min-w-max">
                {starters.map((s, i) => (
                    <button
                        key={i}
                        onClick={() => handleSend(s.label)}
                        className="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-800 rounded-full text-xs font-semibold border border-brand-200 transition-colors flex items-center gap-1.5"
                    >
                        <span>{s.icon}</span>
                        <span>{s.label}</span>
                    </button>
                ))}
            </div>
        </div>
      )}

      {/* Input */}
      <div className="p-4 bg-white border-t border-brand-100">
        <form 
          onSubmit={(e) => { e.preventDefault(); handleSend(); }}
          className="flex gap-2"
        >
          <input
            type="text"
            className="flex-grow px-4 py-2.5 rounded-full border-2 border-brand-50 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none text-sm font-medium transition-all"
            placeholder="Mesajınızı yazın..."
            value={input}
            onChange={(e) => setInput(e.target.value)}
            disabled={isTyping}
          />
          <button
            type="submit"
            disabled={!input.trim() || isTyping}
            className={`w-11 h-11 rounded-full flex items-center justify-center transition-all shadow-md
              ${!input.trim() || isTyping 
                ? 'bg-gray-200 text-gray-400' 
                : 'bg-brand-600 text-white hover:bg-brand-700 active:scale-90'}`}
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 transform rotate-90" viewBox="0 0 20 20" fill="currentColor">
              <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
            </svg>
          </button>
        </form>
      </div>
    </div>
  );
};

export default ChatInterface;