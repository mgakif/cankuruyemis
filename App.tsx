import React, { useState, useEffect } from 'react';
import Header from './components/Header';
import InputSection from './components/InputSection';
import ResultCard from './components/ResultCard';
import LoginForm from './components/LoginForm';
import { generatePostContent, generateVisualContent } from './services/geminiService';
import { LoadingState, GeneratedContent, GenerationMode, VisualGenerationType, AspectRatio } from './types';

const App: React.FC = () => {
  const [isAuthenticated, setIsAuthenticated] = useState<boolean>(false);
  const [loadingState, setLoadingState] = useState<LoadingState>(LoadingState.IDLE);
  const [result, setResult] = useState<GeneratedContent | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    // Check if user was previously logged in
    const authStatus = localStorage.getItem('canKuruyemisAuth');
    if (authStatus === 'true') {
      setIsAuthenticated(true);
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

  const handleGenerate = async (
      text: string, 
      file: File | null, 
      mode: GenerationMode, 
      visualType: VisualGenerationType = VisualGenerationType.ADVERTISEMENT, 
      includeLogo: boolean = false,
      aspectRatio: AspectRatio = '1:1'
  ) => {
    setLoadingState(LoadingState.LOADING);
    setError(null);
    setResult(null);

    try {
      if (mode === GenerationMode.TEXT) {
        const responseData = await generatePostContent(text, file);
        setResult({
          type: GenerationMode.TEXT,
          content: responseData.content,
          tokenUsage: responseData.usage
        });
      } else {
        let logoBase64: string | null = null;
        if (includeLogo) {
            logoBase64 = localStorage.getItem('canKuruyemisLogo');
            if (!logoBase64) {
                alert("Logo bulunamadı! Lütfen önce sağ üst köşeden logo yükleyiniz.");
            }
        }

        const responseData = await generateVisualContent(text, file, visualType, logoBase64, aspectRatio);
        setResult({
          type: GenerationMode.IMAGE,
          content: responseData.content,
          tokenUsage: responseData.usage
        });
      }
      
      setLoadingState(LoadingState.SUCCESS);
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
          <h2 className="text-2xl font-bold text-brand-900 mb-2">Hoşgeldin, Şafak Esnafım! 👋</h2>
          <p className="text-brand-800">
            Bugün tezgahta ne var? Fotoğraf yükle, metin yazdır veya 
            <span className="font-bold text-brand-600"> yeni Stüdyo Modu</span> ile ürünlerinin profesyonel fotoğraflarını çektir!
          </p>
        </div>

        <InputSection 
          onGenerate={handleGenerate} 
          isGenerating={loadingState === LoadingState.LOADING} 
        />

        {loadingState === LoadingState.ERROR && (
          <div className="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded shadow-sm">
            <div className="flex">
              <div className="flex-shrink-0">
                <svg className="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                </svg>
              </div>
              <div className="ml-3">
                <p className="text-sm text-red-700">
                  {error}
                </p>
              </div>
            </div>
          </div>
        )}

        {loadingState === LoadingState.SUCCESS && result && (
          <ResultCard result={result} />
        )}
      </main>
    </div>
  );
};

export default App;