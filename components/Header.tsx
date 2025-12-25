import React, { useState, useEffect, useRef } from 'react';

interface HeaderProps {
  onLogout?: () => void;
}

const Header: React.FC<HeaderProps> = ({ onLogout }) => {
  const [logo, setLogo] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    const savedLogo = localStorage.getItem('canKuruyemisLogo');
    if (savedLogo) {
      setLogo(savedLogo);
    }
  }, []);

  const handleLogoUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    if (event.target.files && event.target.files[0]) {
      const file = event.target.files[0];
      const reader = new FileReader();
      
      reader.onloadend = () => {
        const base64String = reader.result as string;
        if (base64String.length > 3000000) {
          alert("Logo boyutu çok büyük! Lütfen daha küçük bir dosya seçin.");
          return;
        }
        setLogo(base64String);
        localStorage.setItem('canKuruyemisLogo', base64String);
      };
      
      reader.readAsDataURL(file);
    }
  };

  const triggerUpload = () => {
    fileInputRef.current?.click();
  };

  return (
    <header className="bg-brand-900 text-white shadow-lg sticky top-0 z-50">
      <div className="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
        <div className="flex items-center space-x-4">
          <div 
            onClick={triggerUpload}
            className="relative group cursor-pointer bg-white p-1 rounded-full border-2 border-brand-500 w-14 h-14 flex-shrink-0 flex items-center justify-center overflow-hidden"
            title="Logoyu değiştirmek için tıkla"
          >
            {logo ? (
              <img src={logo} alt="Can Kuruyemiş Logo" className="w-full h-full object-cover rounded-full" />
            ) : (
              <span className="text-2xl">🌰</span>
            )}
            <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all flex items-center justify-center">
               <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-white opacity-0 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
               </svg>
            </div>
          </div>
          <input 
            type="file" 
            ref={fileInputRef} 
            onChange={handleLogoUpload} 
            className="hidden" 
            accept="image/*"
          />
          
          <div>
            <h1 className="text-2xl font-bold leading-tight tracking-tight">Can Kuruyemiş</h1>
            <p className="text-xs text-brand-200 uppercase tracking-wider font-medium">Sosyal Medya Asistanı</p>
          </div>
        </div>

        {onLogout && (
          <button 
            onClick={onLogout}
            className="p-2 rounded-full hover:bg-brand-800 transition-colors text-brand-300 hover:text-white"
            title="Güvenli Çıkış"
          >
            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        )}
      </div>
    </header>
  );
};

export default Header;