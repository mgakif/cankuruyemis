export interface TokenUsage {
  promptTokens: number;
  responseTokens: number;
  totalTokens: number;
}

export interface GeneratedContent {
  type: 'TEXT' | 'IMAGE';
  content: string; // Text content or Base64 Image URL
  tokenUsage?: TokenUsage;
}

export enum LoadingState {
  IDLE = 'IDLE',
  LOADING = 'LOADING',
  SUCCESS = 'SUCCESS',
  ERROR = 'ERROR'
}

export enum GenerationMode {
  TEXT = 'TEXT',
  IMAGE = 'IMAGE'
}

export enum VisualGenerationType {
  ENHANCE = 'ENHANCE', // Profesyonelleştirme
  ADVERTISEMENT = 'ADVERTISEMENT' // Reklam/Tanıtım
}

export type AspectRatio = '1:1' | '16:9' | '9:16' | '4:3' | '3:4';
