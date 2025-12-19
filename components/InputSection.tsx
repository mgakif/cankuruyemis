import React, { useState, useRef } from 'react';
import { GenerationMode, VisualGenerationType, AspectRatio } from '../types';

interface InputSectionProps {
  onGenerate: (text: string, file: File | null, mode: GenerationMode, visualType?: VisualGenerationType, includeLogo?: boolean, aspectRatio?: AspectRatio) => void;
  isGenerating: boolean;
}

const InputSection: React.FC<InputSectionProps> = ({ onGenerate, isGenerating }) => {
  const [mode, setMode] = useState<GenerationMode>(GenerationMode.TEXT);
  const [visualType, setVisualType] = useState<VisualGenerationType>(VisualGenerationType.ADVERTISEMENT);
  const [includeLogo, setIncludeLogo] = useState<boolean>(false);
  const [aspectRatio, setAspectRatio] = useState<AspectRatio>('1:1');
  
  const [text, setText] = useState('');
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [previewUrl, setPreviewUrl] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

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
    if (mode === GenerationMode.TEXT && !text && !selectedFile) return;
    if (mode === GenerationMode.IMAGE) {
        // Validation for Enhance mode: Image is mandatory
        if (visualType === VisualGenerationType.ENHANCE && !selectedFile) {
            alert("Profesyonelleştirme modu için lütfen bir fotoğraf yükleyin.");
            return;
        }
        if (visualType === VisualGenerationType.ADVERTISEMENT && !text && !selectedFile) return;
    }
    
    onGenerate(text, selectedFile, mode, visualType, includeLogo, aspectRatio);
  };

  return (
    <div className="bg-white rounded-xl shadow-md overflow-hidden mb-6 border border-brand-100">
      {/* Tabs */}
      <div className="flex bg-gray-100">
        <button
          className={`flex-1 py-4 text-sm font-bold flex items-center justify-center gap-2 transition-all duration-200 
            ${mode === GenerationMode.TEXT 
              ? 'bg-white text-brand-800 border-t-4 border-t-brand-600 shadow-[0_2px_4px_rgba(0,0,0,0.05)] z-10' 
              : 'bg-gray-50 text-gray-500 border-t-4 border-t-transparent hover:bg-gray-100 hover:text-brand-700'
            }`}
          onClick={() => setMode(GenerationMode.TEXT)}
        >
          <svg xmlns="http://www.w3.org/2000/svg" className={`h-5 w-5 ${mode === GenerationMode.TEXT ? 'text-brand-600' : 'text-gray-400'}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Metin Yazarı
        </button>
        <button
          className={`flex-1 py-4 text-sm font-bold flex items-center justify-center gap-2 transition-all duration-200 
            ${mode === GenerationMode.IMAGE 
              ? 'bg-white text-brand-800 border-t-4 border-t-brand-600 shadow-[0_2px_4px_rgba(0,0,0,0.05)] z-10' 
              : 'bg-gray-50 text-gray-500 border-t-4 border-t-transparent hover:bg-gray-100 hover:text-brand-700'
            }`}
          onClick={() => setMode(GenerationMode.IMAGE)}
        >
          <svg xmlns="http://www.w3.org/2000/svg" className={`h-5 w-5 ${mode === GenerationMode.IMAGE ? 'text-brand-600' : 'text-gray-400'}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Görsel Stüdyosu
        </button>
      </div>

      <div className="p-6">
        <form onSubmit={handleSubmit}>

          {/* Image Mode Options */}
          {mode === GenerationMode.IMAGE && (
            <div className="mb-6 bg-brand-50 p-4 rounded-lg border border-brand-100 space-y-4">
                {/* 1. Operation Type */}
                <div>
                    <label className="block text-sm font-bold text-brand-900 mb-2">Ne yapmak istiyorsun?</label>
                    <div className="flex flex-col sm:flex-row gap-4">
                        <label className="flex items-center gap-2 cursor-pointer bg-white px-3 py-2 rounded border border-brand-200 hover:border-brand-400 transition-colors w-full sm:w-auto">
                            <input 
                                type="radio" 
                                name="visualType" 
                                checked={visualType === VisualGenerationType.ENHANCE}
                                onChange={() => setVisualType(VisualGenerationType.ENHANCE)}
                                className="text-brand-600 focus:ring-brand-500"
                            />
                            <span className="text-sm text-gray-800">📸 Mevcut Fotoğrafı Profesyonelleştir</span>
                        </label>
                        <label className="flex items-center gap-2 cursor-pointer bg-white px-3 py-2 rounded border border-brand-200 hover:border-brand-400 transition-colors w-full sm:w-auto">
                            <input 
                                type="radio" 
                                name="visualType" 
                                checked={visualType === VisualGenerationType.ADVERTISEMENT}
                                onChange={() => setVisualType(VisualGenerationType.ADVERTISEMENT)}
                                className="text-brand-600 focus:ring-brand-500"
                            />
                            <span className="text-sm text-gray-800">✨ Reklam / Tanıtım Görseli Oluştur</span>
                        </label>
                    </div>
                </div>

                {/* 2. Aspect Ratio Selector */}
                <div>
                     <label className="block text-sm font-bold text-brand-900 mb-2">Görsel Boyutu (Format)</label>
                     <div className="flex gap-3">
                        <button
                            type="button"
                            onClick={() => setAspectRatio('1:1')}
                            className={`flex flex-col items-center justify-center p-2 rounded-lg border-2 w-20 h-20 transition-all ${aspectRatio === '1:1' ? 'border-brand-500 bg-brand-100 text-brand-800 shadow-sm' : 'border-gray-200 bg-white text-gray-500 hover:bg-gray-50'}`}
                        >
                            <div className="w-8 h-8 border-2 border-current mb-1 bg-current opacity-20"></div>
                            <span className="text-xs font-medium">Kare</span>
                            <span className="text-[10px] opacity-70">1:1</span>
                        </button>

                        <button
                            type="button"
                            onClick={() => setAspectRatio('9:16')}
                            className={`flex flex-col items-center justify-center p-2 rounded-lg border-2 w-20 h-20 transition-all ${aspectRatio === '9:16' ? 'border-brand-500 bg-brand-100 text-brand-800 shadow-sm' : 'border-gray-200 bg-white text-gray-500 hover:bg-gray-50'}`}
                        >
                            <div className="w-5 h-8 border-2 border-current mb-1 bg-current opacity-20"></div>
                            <span className="text-xs font-medium">Hikaye</span>
                            <span className="text-[10px] opacity-70">9:16</span>
                        </button>

                        <button
                            type="button"
                            onClick={() => setAspectRatio('16:9')}
                            className={`flex flex-col items-center justify-center p-2 rounded-lg border-2 w-20 h-20 transition-all ${aspectRatio === '16:9' ? 'border-brand-500 bg-brand-100 text-brand-800 shadow-sm' : 'border-gray-200 bg-white text-gray-500 hover:bg-gray-50'}`}
                        >
                            <div className="w-8 h-5 border-2 border-current mb-1 bg-current opacity-20"></div>
                            <span className="text-xs font-medium">Yatay</span>
                            <span className="text-[10px] opacity-70">16:9</span>
                        </button>
                     </div>
                </div>
                
                {/* 3. Logo Option (Only for Ad) */}
                {visualType === VisualGenerationType.ADVERTISEMENT && (
                     <div className="pt-2 border-t border-brand-100">
                        <label className="flex items-center gap-2 cursor-pointer select-none">
                            <input 
                                type="checkbox"
                                checked={includeLogo}
                                onChange={(e) => setIncludeLogo(e.target.checked)}
                                className="rounded text-brand-600 focus:ring-brand-500"
                            />
                            <span className="text-sm font-medium text-brand-800">Logoyu tasarıma ekle (Sağ üstten yüklediğiniz logo kullanılır)</span>
                        </label>
                     </div>
                )}
            </div>
          )}
          
          {/* Universal Image Upload Area */}
          <div className="mb-4">
              <label className="block text-sm font-medium text-gray-700 mb-2">
              {mode === GenerationMode.TEXT 
                ? "Görsel Yükle (Varsa - Analiz için)" 
                : visualType === VisualGenerationType.ENHANCE 
                    ? "Düzenlenecek Fotoğrafı Yükle (Zorunlu)"
                    : "Referans Fotoğraf (İsteğe Bağlı)"}
              </label>
            {!previewUrl ? (
              <div 
                onClick={() => fileInputRef.current?.click()}
                className={`border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors
                    ${mode === GenerationMode.IMAGE && visualType === VisualGenerationType.ENHANCE ? 'border-brand-400 bg-brand-50 hover:bg-brand-100' : 'border-gray-300 hover:bg-gray-50'}`}
              >
                <p className="text-sm text-gray-500">
                  {mode === GenerationMode.IMAGE && visualType === VisualGenerationType.ENHANCE 
                    ? "Lütfen profesyonelleştirmek istediğiniz fotoğrafı buraya yükleyin" 
                    : "Fotoğraf seçmek için tıklayın"}
                </p>
                <input 
                  type="file" 
                  ref={fileInputRef}
                  onChange={handleFileChange} 
                  className="hidden" 
                  accept="image/*"
                />
              </div>
            ) : (
              <div className="relative rounded-lg overflow-hidden border border-brand-200 bg-gray-100">
                <img src={previewUrl} alt="Preview" className="w-full h-32 object-cover opacity-90" />
                <button
                  type="button"
                  onClick={clearFile}
                  className="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full hover:bg-red-600 shadow-sm"
                  title="Görseli kaldır"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            )}
          </div>

          {/* Common Text Area */}
          <div className="mb-6">
            <label htmlFor="prompt" className="block text-sm font-medium text-gray-700 mb-2">
              {mode === GenerationMode.TEXT 
                ? "Konu veya Özel İstek" 
                : visualType === VisualGenerationType.ENHANCE 
                    ? "Fotoğrafta neyi düzeltelim?"
                    : "Nasıl bir reklam görseli istiyorsun?"}
            </label>
            <textarea
              id="prompt"
              rows={3}
              className="w-full p-3 rounded-lg border border-brand-200 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white text-gray-900 placeholder-gray-500"
              placeholder={
                  mode === GenerationMode.TEXT ? "Örn: Hafta sonuna özel karışık çerez indirimi..." 
                  : visualType === VisualGenerationType.ENHANCE ? "Örn: Işığı düzelt, daha iştah açıcı yap..."
                  : "Örn: Lüks karışık çerez tabağı, yanına 'Afiyet Olsun' yazısı ekle..."
                }
              value={text}
              onChange={(e) => setText(e.target.value)}
            />
          </div>

          {/* Submit Button */}
          <button
            type="submit"
            disabled={isGenerating || (mode === GenerationMode.IMAGE && visualType === VisualGenerationType.ENHANCE && !selectedFile)}
            className={`w-full py-3 px-4 rounded-lg font-semibold text-white shadow-md transition-all flex justify-center items-center gap-2
              ${isGenerating || (mode === GenerationMode.IMAGE && visualType === VisualGenerationType.ENHANCE && !selectedFile)
                ? 'bg-brand-300 cursor-not-allowed' 
                : 'bg-brand-600 hover:bg-brand-700 hover:shadow-lg active:transform active:scale-[0.98]'
              }`}
          >
            {isGenerating ? (
              <>
                <svg className="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>İşleniyor...</span>
              </>
            ) : (
              <>
                {mode === GenerationMode.TEXT && (
                  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                )}
                {mode === GenerationMode.IMAGE && visualType === VisualGenerationType.ENHANCE && (
                   <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                )}
                {mode === GenerationMode.IMAGE && visualType === VisualGenerationType.ADVERTISEMENT && (
                   <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                )}
                
                <span>
                    {mode === GenerationMode.TEXT ? "İçerik Oluştur" 
                    : visualType === VisualGenerationType.ENHANCE ? "Fotoğrafı Profesyonelleştir" 
                    : "Reklam Görseli Oluştur"}
                </span>
              </>
            )}
          </button>
        </form>
      </div>
    </div>
  );
};

export default InputSection;