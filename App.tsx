import React, { useState, useEffect } from 'react';
import Header from './components/Header';
import InputSection from './components/InputSection';
import ResultCard from './components/ResultCard';
import LoginForm from './components/LoginForm';
import ChatInterface from './components/ChatInterface';
import SavedList from './components/SavedList';
import { generatePostContent, generateVisualContent } from './services/geminiService';
import { LoadingState, GeneratedContent, GenerationMode, VisualGenerationType, AspectRatio, TextTone, SavedItem } from './types';

const App: React.FC = () => {
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);
  const [loadingState, setLoadingState] = useState<LoadingState>(LoadingState.IDLE);
  const [result, setResult] = useState<GeneratedContent | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [activeMode, setActiveMode] = useState<GenerationMode>(GenerationMode.TEXT);
  const [savedItems, setSavedItems] = useState<SavedItem[]>([]);
  
  // States to pass from ResultCard to InputSection for the "Design Ad" flow
  const [initialPrompt, setInitialPrompt] = useState<string>('');

  useEffect(() => {
    const authStatus = localStorage.getItem('canKuruyemisAuth');
    if (authStatus === 'true') {
      setIsAuthenticated(true);
    }
    
    const storedItems = localStorage.getItem('canKuruyemisArchive');
    if (storedItems) {
      setSavedItems(JSON.parse(storedItems));
    }
  }, []);

  const handleLogin = () => {
    setIsAuthenticated(true);
    localStorage.setItem('canKuruyemisAuth', 'true');
  };

  const handleLogout = () => {
    setIsAuthenticated(false);
    localStorage.removeItem('canKuruyemisAuth');
  };

  const handleSaveItem = (item: Omit<SavedItem, 'id' | 'timestamp'>) => {
    const newItem: SavedItem = {
      ...item,
      id: Math.random().toString(36).substr(2, 9),
      timestamp: Date.now()
    };
    const updated = [newItem, ...savedItems];
    setSavedItems(updated);
    localStorage.setItem('canKuruyemisArchive', JSON.stringify(updated));
    alert("İçerik Arşive Kaydedildi! 💾");
  };

  const handleDeleteItem = (id: string) => {
    const updated = savedItems.filter(item => item.id !== id);
    setSavedItems(updated);
    localStorage.setItem('canKuruyemisArchive', JSON.stringify(updated));
  };

  const handleDesignAdFromText = (text: string) => {
      // Extract first part of content as a hint for the image prompt
      const hint = text.split('\n').find(l => l.length > 20) || text.substring(0, 100);
      setInitialPrompt(`Analize dayalı profesyonel reklam: ${hint.substring(0, 150)}...`);
      setActiveMode(GenerationMode.IMAGE);
      setResult(null);
      window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleGenerate = async (
      text: string, 
      file: File | null, 
      mode: GenerationMode, 
      visualType: VisualGenerationType = VisualGenerationType.ADVERTISEMENT, 
      includeLogo: boolean = false,
      aspectRatio: AspectRatio = '1:1',
      tone: TextTone = 'friendly'
  ) => {
    setLoadingState(LoadingState.LOADING);
    setError(null);
    setResult(null);

    try {
      if (mode === GenerationMode.TEXT) {
        const responseData = await generatePostContent(text, file, tone);
        setResult({
          type: 'TEXT',
          content: responseData.content,
          tokenUsage: responseData.usage
        });
      } else {
        let logoBase64: string | null = localStorage.getItem('canKuruyemisLogo');
        if (includeLogo && !logoBase64) {
            alert("Dikkat: Logo henüz yüklenmemiş! Sağ üst köşeden logonuzu yükleyebilirsiniz.");
        }

        const responseData = await generateVisualContent(text, file, visualType, logoBase64, aspectRatio);
        setResult({
          type: 'IMAGE',
          content: responseData.content,
          tokenUsage: responseData.usage
        });
      }
      
      setLoadingState(LoadingState.SUCCESS);
      setInitialPrompt(''); // Clear initial prompt after use
    } catch (err: any) {
      setError(err.message || "Bilinmeyen bir hata oluştu.");
      setLoadingState(LoadingState.ERROR);
    }
  };

  if (!isAuthenticated) {
    return <LoginForm onLogin={handleLogin} />;
  }

  return (
    <div className="min-h-screen flex flex-col bg-brand-50 pb-12 animate-fade-in">
      <Header onLogout={handleLogout} />
      
      <main className="flex-grow w-full max-w-3xl mx-auto px-4 py-8">
        <div className="mb-6">
          <h2 className="text-xl sm:text-2xl font-bold text-brand-900 mb-1">Selam, Şafak Esnafım! 👋</h2>
          <p className="text-xs sm:text-sm text-brand-800">Can Kuruyemiş'in dijital vitrinini beraber yönetelim.</p>
        </div>

        <InputSection 
          onGenerate={handleGenerate} 
          onModeChange={(mode) => {
              setActiveMode(mode);
              setResult(null);
              setError(null);
              setInitialPrompt('');
          }}
          isGenerating={loadingState === LoadingState.LOADING}
          currentMode={activeMode}
          initialText={initialPrompt}
        />

        {activeMode === GenerationMode.CHAT && <ChatInterface />}
        
        {activeMode === GenerationMode.SAVED && (
            <SavedList items={savedItems} onDelete={handleDeleteItem} />
        )}

        {activeMode !== GenerationMode.CHAT && activeMode !== GenerationMode.SAVED && (
          <>
            {loadingState === LoadingState.ERROR && (
              <div className="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded shadow-sm flex items-center gap-3">
                <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" /></svg>
                <p className="text-sm text-red-700">{error}</p>
              </div>
            )}

            {loadingState === LoadingState.SUCCESS && result && (
              <ResultCard 
                result={result} 
                onSave={handleSaveItem} 
                onDesignAd={handleDesignAdFromText}
              />
            )}
            
            {loadingState === LoadingState.LOADING && (
                <div className="text-center py-16 bg-white/50 rounded-xl border border-brand-100 shadow-inner">
                    <div className="flex justify-center mb-4">
                        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-600"></div>
                    </div>
                    <p className="text-brand-800 font-bold animate-pulse">İstediğin içerik hazırlanıyor, dükkanı kapatıp beklemeye değer...</p>
                </div>
            )}
          </>
        )}
      </main>
    </div>
  );
};

export default App;