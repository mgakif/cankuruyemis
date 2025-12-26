export interface TokenUsage {
  promptTokens: number;
  responseTokens: number;
  totalTokens: number;
}

export interface GeneratedContent {
  type: 'TEXT' | 'IMAGE' | 'CHAT';
  content: string; // Text content or Base64 Image URL
  tokenUsage?: TokenUsage;
}

export interface SavedItem {
  id: string;
  type: 'TEXT' | 'IMAGE';
  content: string;
  timestamp: number;
  title: string;
}

export enum LoadingState {
  IDLE = 'IDLE',
  LOADING = 'LOADING',
  SUCCESS = 'SUCCESS',
  ERROR = 'ERROR'
}

export enum GenerationMode {
  TEXT = 'TEXT',
  IMAGE = 'IMAGE',
  CHAT = 'CHAT',
  SAVED = 'SAVED'
}

export enum VisualGenerationType {
  ENHANCE = 'ENHANCE', 
  ADVERTISEMENT = 'ADVERTISEMENT'
}

export type AspectRatio = '1:1' | '16:9' | '9:16' | '4:3' | '3:4';

export type TextTone = 'friendly' | 'funny' | 'informative' | 'product_focused' | 'sale';