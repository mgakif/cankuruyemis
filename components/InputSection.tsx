import React, { useState, useRef, useEffect } from 'react';
import { GenerationMode, VisualGenerationType, AspectRatio, TextTone } from '../types';

interface InputSectionProps {
  onGenerate: (text: string, file: File | null, mode: GenerationMode, visualType?: VisualGenerationType, includeLogo?: boolean, aspectRatio?: AspectRatio, tone?: TextTone) => void;
  onModeChange: (mode: GenerationMode) => void;
  isGenerating: boolean;
  currentMode: GenerationMode;
  initialText?: string;
}

const InputSection: React.FC<InputSectionProps> = ({ onGenerate, onModeChange, isGenerating, currentMode, initialText = '' }) => {
  const [visualType, setVisualType] = useState<VisualGenerationType>(VisualGenerationType.ADVERTISEMENT);
  const [includeLogo, setIncludeLogo] = useState<boolean>(true);
  const [aspectRatio, setAspectRatio] = useState<AspectRatio>('1:1');
  const [tone, setTone] = useState<TextTone>('friendly');
  
  const [text, setText] = useState('');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Synchronize text with initialText when it changes (e.g., from Design Ad flow)
  useEffect(() => {
    if (initialText) {
      setText(initialText);
    }
  }, [initialText]);

  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    if (event.target.files && event.target.files[0]) {
      const file = event.target.files[0];
      setSelectedFile(file);
      const url = URL.createObjectURL(file);
      setPreviewUrl(url);
    }
  };

  const clearFile = () => {
    setSelectedFile(null);
    setPreviewUrl(null);
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (currentMode === GenerationMode.TEXT && !text && !selectedFile) return;
    if (currentMode === GenerationMode.IMAGE) {
        if (visualType === VisualGenerationType.ENHANCE && !selectedFile) {
            alert("Profesyonelleştirme modu için lütfen bir fotoğraf yükleyin.");
            return;
        }
        if (visualType === VisualGenerationType.ADVERTISEMENT && !text && !selectedFile) return;
    }
    
    onGenerate(text, selectedFile, currentMode, visualType, includeLogo, aspectRatio, tone);
  };

  const tones: { id: TextTone; label: string; icon: string }[] = [
    { id: 'friendly', label: 'Samimi', icon: '😊' },
    { id: 'funny', label: 'Esprili', icon: '😂' },
    { id: 'informative', label: 'Bilgi Veren', icon: '🧠' },
    { id: 'product_focused', label: 'Lezzet Odaklı', icon: '😋' },
    { id: 'sale', label: 'Kampanya', icon: '📢' },
  ];

  const getTabClass = (mode: GenerationMode) => `flex-1 py-4 text-[10px] xs:text-xs sm:text-sm font-bold flex items-center justify-center gap-1.5 transition-all duration-200 
    ${currentMode === mode 
      ? 'bg-white text-brand-800 border-t-4 border-t-brand-600 shadow-[0_2px_4px_rgba(0,0,0,0.05)] z-10' 
      : 'bg-gray-50 text-gray-500 border-t-4 border-t-transparent hover:bg-gray-100 hover:text-brand-700'
    }`;

  return (
    <div className="bg-white rounded-xl shadow-md overflow-hidden mb-6 border border-brand-100">
      <div className="flex bg-gray-100 border-b border-gray-200">
        <button type="button" className={getTabClass(GenerationMode.TEXT)} onClick={() => onModeChange(GenerationMode.TEXT)}>
          <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
          <span className="hidden xs:inline">Metin Yazarı</span>
          <span className="xs:hidden">Metin</span>
        </button>
        <button type="button" className={getTabClass(GenerationMode.IMAGE)} onClick={() => onModeChange(GenerationMode.IMAGE)}>
          <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          <span className="hidden xs:inline">Stüdyo</span>
          <span className="xs:hidden">Görsel</span>
        </button>
        <button type="button" className={getTabClass(GenerationMode.CHAT)} onClick={() => onModeChange(GenerationMode.CHAT)}>
          <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
          <span className="hidden xs:inline">Asistan</span>
          <span className="xs:hidden">Sohbet</span>
        </button>
        <button type="button" className={getTabClass(GenerationMode.SAVED)} onClick={() => onModeChange(GenerationMode.SAVED)}>
          <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
          <span className="hidden xs:inline">Arşiv</span>
          <span className="xs:hidden">Kayıtlı</span>
        </button>
      </div>

      {currentMode !== GenerationMode.CHAT && currentMode !== GenerationMode.SAVED && (
        <div className="p-6">
          <form onSubmit={handleSubmit}>
            {currentMode === GenerationMode.TEXT && (
              <div className="mb-6">
                  <label className="block text-sm font-bold text-brand-900 mb-3">İçerik Stili</label>
                  <div className="flex flex-wrap gap-2">
                      {tones.map((t) => (
                          <button
                              key={t.id}
                              type="button"
                              onClick={() => setTone(t.id)}
                              className={`flex items-center gap-2 px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all border-2
                                  ${tone === t.id 
                                      ? 'bg-brand-600 border-brand-600 text-white shadow-md transform scale-105' 
                                      : 'bg-white border-brand-100 text-brand-800 hover:border-brand-300'}`}
                          >
                              <span>{t.icon}</span>
                              <span>{t.label}</span>
                          </button>
                      ))}
                  </div>
              </div>
            )}

            {currentMode === GenerationMode.IMAGE && (
              <div className="mb-6 bg-brand-50 p-4 rounded-lg border border-brand-100 space-y-4">
                  <div>
                      <label className="block text-sm font-bold text-brand-900 mb-2">Hangi İşlemi Yapalım?</label>
                      <div className="flex flex-col sm:flex-row gap-4">
                          <label className={`flex items-center gap-2 cursor-pointer bg-white px-3 py-2 rounded border-2 transition-all w-full sm:w-auto ${visualType === VisualGenerationType.ENHANCE ? 'border-brand-500 shadow-sm' : 'border-brand-100'}`}>
                              <input type="radio" name="visualType" checked={visualType === VisualGenerationType.ENHANCE} onChange={() => setVisualType(VisualGenerationType.ENHANCE)} className="text-brand-600 focus:ring-brand-500" />
                              <span className="text-sm font-semibold text-gray-800">📸 Profesyonelleştir</span>
                          </label>
                          <label className={`flex items-center gap-2 cursor-pointer bg-white px-3 py-2 rounded border-2 transition-all w-full sm:w-auto ${visualType === VisualGenerationType.ADVERTISEMENT ? 'border-brand-500 shadow-sm' : 'border-brand-100'}`}>
                              <input type="radio" name="visualType" checked={visualType === VisualGenerationType.ADVERTISEMENT} onChange={() => setVisualType(VisualGenerationType.ADVERTISEMENT)} className="text-brand-600 focus:ring-brand-500" />
                              <span className="text-sm font-semibold text-gray-800">✨ Reklam Tasarla</span>
                          </label>
                      </div>
                  </div>

                  <div>
                       <label className="block text-sm font-bold text-brand-900 mb-2">Görsel Formatı</label>
                       <div className="flex gap-3">
                          {['1:1', '9:16', '16:9'].map((ratio) => (
                              <button
                                key={ratio}
                                type="button"
                                onClick={() => setAspectRatio(ratio as AspectRatio)}
                                className={`flex flex-col items-center justify-center p-2 rounded-lg border-2 w-16 h-16 sm:w-20 sm:h-20 transition-all ${aspectRatio === ratio ? 'border-brand-500 bg-brand-100 text-brand-800 shadow-sm' : 'border-gray-200 bg-white text-gray-500 hover:bg-gray-50'}`}
                              >
                                  <div className={`border-2 border-current mb-1 bg-current opacity-20 ${ratio === '1:1' ? 'w-6 h-6 sm:w-8 sm:h-8' : ratio === '9:16' ? 'w-4 h-6 sm:w-5 sm:h-8' : 'w-6 h-4 sm:w-8 sm:h-5'}`}></div>
                                  <span className="text-[10px] sm:text-xs font-medium">{ratio === '1:1' ? 'Kare' : ratio === '9:16' ? 'Hikaye' : 'Yatay'}</span>
                              </button>
                          ))}
                       </div>
                  </div>
                  
                  {visualType === VisualGenerationType.ADVERTISEMENT && (
                       <div className="pt-2 border-t border-brand-100">
                          <label className="flex items-center gap-2 cursor-pointer select-none">
                              <input type="checkbox" checked={includeLogo} onChange={(e) => setIncludeLogo(e.target.checked)} className="w-5 h-5 rounded text-brand-600 focus:ring-brand-500" />
                              <span className="text-xs sm:text-sm font-bold text-brand-900">Logomu Görsele Ekle 🌰</span>
                          </label>
                       </div>
                  )}
              </div>
            )}
            
            <div className="mb-4">
                <label className="block text-sm font-bold text-gray-700 mb-2">
                {currentMode === GenerationMode.TEXT ? "Görsel Analizi (İsteğe Bağlı)" : visualType === VisualGenerationType.ENHANCE ? "Kaynak Fotoğraf (Zorunlu)" : "Referans Fotoğraf (İsteğe Bağlı)"}
                </label>
              {!previewUrl ? (
                <div 
                  onClick={() => fileInputRef.current?.click()}
                  className={`border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors
                      ${currentMode === GenerationMode.IMAGE && visualType === VisualGenerationType.ENHANCE ? 'border-brand-400 bg-brand-50 hover:bg-brand-100' : 'border-gray-300 hover:bg-gray-50'}`}
                >
                  <p className="text-sm text-gray-500">Fotoğraf seçmek için tıklayın veya sürükleyin</p>
                  <input type="file" ref={fileInputRef} onChange={handleFileChange} className="hidden" accept="image/*" />
                </div>
              ) : (
                <div className="relative rounded-lg overflow-hidden border-2 border-brand-200 bg-gray-100">
                  <img src={previewUrl} alt="Preview" className="w-full h-40 object-contain bg-black" />
                  <button type="button" onClick={clearFile} className="absolute top-2 right-2 bg-red-600 text-white p-1.5 rounded-full hover:bg-red-700 shadow-lg">
                      <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>
                  </button>
                </div>
              )}
            </div>

            <div className="mb-4">
              <label htmlFor="prompt" className="block text-sm font-bold text-gray-700 mb-2">İçerik Konusu / Reklam Mesajı</label>
              <textarea
                id="prompt"
                rows={4}
                className="w-full p-4 rounded-xl border-2 border-brand-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white text-brand-950 font-medium placeholder-gray-400 shadow-inner"
                placeholder={currentMode === GenerationMode.TEXT ? "Örn: Taze kavrulmuş fındıklarımız tezgahta..." : "Örn: Klasik bir kuruyemiş dükkanı atmosferi, merkezde karışık lüks çerezler..."}
                value={text}
                onChange={(e) => setText(e.target.value)}
              />
            </div>

            <button
              type="submit"
              disabled={isGenerating || (currentMode === GenerationMode.IMAGE && visualType === VisualGenerationType.ENHANCE && !selectedFile)}
              className={`w-full py-4 px-4 rounded-xl font-bold text-white shadow-lg transition-all flex justify-center items-center gap-3 text-lg
                ${isGenerating ? 'bg-brand-300 cursor-not-allowed' : 'bg-brand-600 hover:bg-brand-700 active:scale-[0.98]'}`}
            >
              {isGenerating ? (
                <><div className="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></div><span>İşlem Sürüyor...</span></>
              ) : (
                <span>{currentMode === GenerationMode.TEXT ? "Metin Üret" : "Tasarımı Hazırla"}</span>
              )}
            </button>
          </form>
        </div>
      )}
    </div>
  );
};

export default InputSection;