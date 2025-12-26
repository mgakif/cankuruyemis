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

  const getTabClass = (mode: GenerationMode) => `flex-1 py-4 text-[10px] xs:text-xs sm:text-sm font-bold flex items-center justify-center gap-1.5 transition-all duration-200 
    ${currentMode === mode 
      ? 'bg-white text-brand-800 border-t-4 border-t-brand-600 shadow-sm z-10' 
      : 'bg-gray-50 text-gray-500 border-t-4 border-t-transparent hover:bg-gray-100 hover:text-brand-700'
    }`;

  return (
    <div className="bg-white rounded-xl shadow-md overflow-hidden mb-6 border border-brand-100">
      <div className="flex bg-gray-100 border-b border-gray-200">
        <button type="button" className={getTabClass(GenerationMode.TEXT)} onClick={() => onModeChange(GenerationMode.TEXT)}>
          <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
          <span className="hidden xs:inline">Metin Yazarı</span><span className="xs:hidden">Metin</span>
        </button>
        <button type="button" className={getTabClass(GenerationMode.IMAGE)} onClick={() => onModeChange(GenerationMode.IMAGE)}>
          <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          <span className="hidden xs:inline">Stüdyo</span><span className="xs:hidden">Görsel</span>
        </button>
        <button type="button" className={getTabClass(GenerationMode.CHAT)} onClick={() => onModeChange(GenerationMode.CHAT)}>
          <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
          <span className="hidden xs:inline">Asistan</span><span className="xs:hidden">Sohbet</span>
        </button>
        <button type="button" className={getTabClass(GenerationMode.SAVED)} onClick={() => onModeChange(GenerationMode.SAVED)}>
          <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
          <span className="hidden xs:inline">Arşiv</span><span className="xs:hidden">Kayıtlı</span>
        </button>
      </div>

      {currentMode !== GenerationMode.CHAT && currentMode !== GenerationMode.SAVED && (
        <div className="p-6">
          <form onSubmit={handleSubmit}>
            {currentMode === GenerationMode.IMAGE && (
              <div className="mb-6 bg-amber-50 p-4 rounded-lg border border-amber-100 flex justify-between items-center">
                  <div>
                      <p className="text-[10px] font-bold text-amber-800 uppercase tracking-widest">Geliştirici Modu</p>
                      <p className="text-xs text-amber-700">Drive yüklemesini hızlıca test etmek ister misin?</p>
                  </div>
                  <button 
                    type="button" 
                    onClick={onTestImage}
                    className="bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-md hover:bg-amber-700 active:scale-95 transition-all flex items-center gap-1"
                  >
                    <span>🔧 Test Görseli Al</span>
                  </button>
              </div>
            )}

            <div className="mb-4">
                <label className="block text-sm font-bold text-gray-700 mb-2">Görsel Seçin</label>
              {!previewUrl ? (
                <div 
                  onClick={() => fileInputRef.current?.click()}
                  className="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer border-gray-300 hover:bg-gray-50 transition-colors"
                >
                  <p className="text-sm text-gray-500">Fotoğraf Yüklemek İçin Tıklayın</p>
                  <input type="file" ref={fileInputRef} onChange={handleFileChange} className="hidden" accept="image/*" />
                </div>
              ) : (
                <div className="relative rounded-lg overflow-hidden border-2 border-brand-200 bg-gray-100">
                  <img src={previewUrl} alt="Preview" className="w-full h-40 object-contain bg-black" />
                  <button type="button" onClick={clearFile} className="absolute top-2 right-2 bg-red-600 text-white p-1.5 rounded-full shadow-lg">
                      <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>
                  </button>
                </div>
              )}
            </div>

            <div className="mb-4">
              <label className="block text-sm font-bold text-gray-700 mb-2">İçerik Detayları</label>
              <textarea
                rows={3}
                className="w-full p-4 rounded-xl border-2 border-brand-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none text-brand-950"
                placeholder="Örn: Sıcak kavrulmuş leblebi reklamı..."
                value={text}
                onChange={(e) => setText(e.target.value)}
              />
            </div>

            <button
              type="submit"
              disabled={isGenerating}
              className={`w-full py-4 rounded-xl font-bold text-white shadow-lg transition-all text-lg ${isGenerating ? 'bg-brand-300 cursor-not-allowed' : 'bg-brand-600 hover:bg-brand-700 active:scale-[0.98]'}`}
            >
              {isGenerating ? "Hazırlanıyor..." : "Üretmeye Başla"}
            </button>
          </form>
        </div>
      )}
    </div>
  );
};

export default InputSection;