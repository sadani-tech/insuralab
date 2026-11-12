"use client";

import { useSyncExternalStore } from "react";

import {
  ArrowDown,
  ArrowLeft,
  ArrowRight,
  ArrowUp,
  Minus,
  Pause,
  Play,
  Plus,
  RotateCcw,
} from "lucide-react";

export type SceneAction = "in" | "out" | "reset" | "left" | "right" | "up" | "down";

const subscribeMotion = (listener: () => void) => {
  const media = window.matchMedia("(prefers-reduced-motion: reduce)");
  media.addEventListener("change", listener);
  return () => media.removeEventListener("change", listener);
};
const getReduced = () => window.matchMedia("(prefers-reduced-motion: reduce)").matches;

export function SceneControls({
  paused,
  onPause,
  onAction,
}: {
  paused: boolean;
  onPause: () => void;
  onAction: (action: SceneAction) => void;
}) {
  const reduced = useSyncExternalStore(subscribeMotion, getReduced, () => false);
  return (
    <div className="scene-controls" aria-label="Kontrol peta">
      <button
        aria-label={
          reduced
            ? "Gerak dinonaktifkan sesuai preferensi perangkat"
            : paused
              ? "Lanjutkan gerak"
              : "Jeda gerak"
        }
        onClick={onPause}
        disabled={reduced}
      >
        {paused || reduced ? <Play size={15} /> : <Pause size={15} />}
      </button>
      <button aria-label="Perbesar peta" onClick={() => onAction("in")}>
        <Plus size={18} />
      </button>
      <button aria-label="Perkecil peta" onClick={() => onAction("out")}>
        <Minus size={18} />
      </button>
      <button aria-label="Putar ke kiri" onClick={() => onAction("left")}>
        <ArrowLeft size={16} />
      </button>
      <button aria-label="Putar ke kanan" onClick={() => onAction("right")}>
        <ArrowRight size={16} />
      </button>
      <button aria-label="Putar ke atas" onClick={() => onAction("up")}>
        <ArrowUp size={16} />
      </button>
      <button aria-label="Putar ke bawah" onClick={() => onAction("down")}>
        <ArrowDown size={16} />
      </button>
      <button aria-label="Atur ulang kamera" onClick={() => onAction("reset")}>
        <RotateCcw size={16} />
      </button>
    </div>
  );
}
