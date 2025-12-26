import React, { useState, useRef, useEffect } from 'react';
import { GenerationMode, VisualGenerationType, AspectRatio, TextTone } from '../types';

interface InputSectionProps {
  onGenerate: (text: string, file: File | null, mode: GenerationMode, visualType?: VisualGenerationType, includeLogo?: boolean, aspectRatio?: AspectRatio, tone?: TextTone) => void;
  onModeChange: (mode: GenerationMode) => void;
  onTestImage?: () => void;
  isGenerating: boolean;
  currentMode: GenerationMode;
  initialText?: string;
}

const InputSection: React.FC<InputSectionProps> = ({ onGenerate, onModeChange, onTestImage, isGenerating, currentMode, initialText = '' }) => {
  const [visualType, setVisualType] = useState<VisualGenerationType>(VisualGenerationType.ADVERTISEMENT);
  const [includeLogo, setIncludeLogo] = useState<boolean>(true);
  const [aspectRatio, setAspectRatio] = useState<AspectRatio>('1:1');
  const [tone, setTone] = useState<TextTone>('friendly');
  
  const [text, setText] = useState('');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (initialText) setText(initialText);
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
    if (fileInputRef.current) fileInputRef.current.value = '';
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
      ? 'bg-white text-brand-800 border-t-4 border-t-brand-600 shadow-sm z-10' 
      : 'bg-gray-50 text-gray-500 border-t-4 border-t-transparent hover:bg-gray-100'
    }`;

  return (
    <div className="bg-white rounded-xl shadow-md overflow-hidden mb-6 border border-brand-100">
      <div className="flex bg-gray-100 border-b border-gray-200">
        <button type="button" className={getTabClass(GenerationMode.TEXT)} onClick={() => onModeChange(GenerationMode.TEXT)}>
          <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
          <span>Metin Yazarı</span>
        </button>
        <button type="button" className={getTabClass(GenerationMode.IMAGE)} onClick={() => onModeChange(GenerationMode.IMAGE)}>
          <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          <span>Stüdyo</span>
        </button>
        <button type="button" className={getTabClass(GenerationMode.CHAT)} onClick={() => onModeChange(GenerationMode.CHAT)}>
          <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
          <span>Asistan</span>
        </button>
        <button type="button" className={getTabClass(GenerationMode.SAVED)} onClick={() => onModeChange(GenerationMode.SAVED)}>
          <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
          <span>Arşiv</span>
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
                              className={`flex items-center gap-2 px-4 py-2 rounded-full text-xs font-semibold transition-all border-2
                                  ${tone === t.id ? 'bg-brand-600 border-brand-600 text-white shadow-md' : 'bg-white border-brand-100 text-brand-800 hover:border-brand-300'}`}
                          >
                              <span>{t.icon}</span> <span>{t.label}</span>
                          </button>
                      ))}
                  </div>
              </div>
            )}

            {currentMode === GenerationMode.IMAGE && (
              <div className="mb-6 bg-brand-50 p-4 rounded-lg border border-brand-100 space-y-4">
                  <div className="flex justify-between items-center">
                      <label className="block text-sm font-bold text-brand-900">Hangi İşlemi Yapalım?</label>
                      {onTestImage && (
                          <button 
                            type="button" 
                            onClick={onTestImage}
                            className="text-[10px] bg-amber-500 text-white px-2 py-1 rounded shadow-sm hover:bg-amber-600 transition-colors font-bold uppercase tracking-tighter"
                          >
                            Hızlı Drive Testi 🔧
                          </button>
                      )}
                  </div>
                  <div className="flex flex-col sm:flex-row gap-4">
                      <label className={`flex items-center gap-2 cursor-pointer bg-white px-3 py-2 rounded border-2 transition-all w-full sm:w-auto ${visualType === VisualGenerationType.ENHANCE ? 'border-brand-500 shadow-sm' : 'border-brand-100'}`}>
                          <input type="radio" name="visualType" checked={visualType === VisualGenerationType.ENHANCE} onChange={() => setVisualType(VisualGenerationType.ENHANCE)} className="text-brand-600" />
                          <span className="text-sm font-semibold">📸 Profesyonelleştir</span>
                      </label>
                      <label className={`flex items-center gap-2 cursor-pointer bg-white px-3 py-2 rounded border-2 transition-all w-full sm:w-auto ${visualType === VisualGenerationType.ADVERTISEMENT ? 'border-brand-500 shadow-sm' : 'border-brand-100'}`}>
                          <input type="radio" name="visualType" checked={visualType === VisualGenerationType.ADVERTISEMENT} onChange={() => setVisualType(VisualGenerationType.ADVERTISEMENT)} className="text-brand-600" />
                          <span className="text-sm font-semibold">✨ Reklam Tasarla</span>
                      </label>
                  </div>

                  <div>
                       <label className="block text-sm font-bold text-brand-900 mb-2">Format</label>
                       <div className="flex gap-3">
                          {['1:1', '9:16', '16:9'].map((ratio) => (
                              <button
                                key={ratio}
                                type="button"
                                onClick={() => setAspectRatio(ratio as AspectRatio)}
                                className={`flex flex-col items-center justify-center p-2 rounded-lg border-2 w-16 h-16 transition-all ${aspectRatio === ratio ? 'border-brand-500 bg-brand-100 text-brand-800' : 'border-gray-200 bg-white text-gray-500 hover:bg-gray-50'}`}
                              >
                                  <div className={`border-2 border-current mb-1 bg-current opacity-20 ${ratio === '1:1' ? 'w-6 h-6' : ratio === '9:16' ? 'w-4 h-6' : 'w-6 h-4'}`}></div>
                                  <span className="text-[10px] font-medium">{ratio}</span>
                              </button>
                          ))}
                       </div>
                  </div>
              </div>
            )}
            
            <div className="mb-4">
                <label className="block text-sm font-bold text-gray-700 mb-2">Görsel Seçin</label>
              {!previewUrl ? (
                <div 
                  onClick={() => fileInputRef.current?.click()}
                  className="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer border-gray-300 hover:bg-gray-50"
                >
                  <p className="text-sm text-gray-500">Tıklayıp Fotoğraf Yükleyin</p>
                  <input type="file" ref={fileInputRef} onChange={handleFileChange} className="hidden" accept="image/*" />
                </div>
              ) : (
                <div className="relative rounded-lg overflow-hidden border-2 border-brand-200 bg-gray-100">
                  <img src={previewUrl} alt="Preview" className="w-full h-40 object-contain bg-black" />
                  <button type="button" onClick={clearFile} className="absolute top-2 right-2 bg-red-600 text-white p-1.5 rounded-full">
                      <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>
                  </button>
                </div>
              )}
            </div>

            <div className="mb-4">
              <label className="block text-sm font-bold text-gray-700 mb-2">Açıklama</label>
              <textarea
                rows={3}
                className="w-full p-4 rounded-xl border-2 border-brand-100 focus:ring-2 focus:ring-brand-500 outline-none"
                placeholder="Ne anlatmak istiyorsun esnafım?"
                value={text}
                onChange={(e) => setText(e.target.value)}
              />
            </div>

            <button
              type="submit"
              disabled={isGenerating}
              className={`w-full py-4 rounded-xl font-bold text-white shadow-lg transition-all ${isGenerating ? 'bg-brand-300' : 'bg-brand-600 hover:bg-brand-700'}`}
            >
              {isGenerating ? "İşlem Sürüyor..." : "İçeriği Oluştur"}
            </button>
          </form>
        </div>
      )}
    </div>
  );
};

export default InputSection;