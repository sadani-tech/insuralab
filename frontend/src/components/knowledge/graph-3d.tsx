"use client";

import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import ForceGraph3D, { type ForceGraphMethods, type NodeObject } from "react-force-graph-3d";
import {
  Sprite,
  SpriteMaterial,
  CanvasTexture,
  SRGBColorSpace,
  Vector3,
  Spherical,
  PerspectiveCamera,
} from "three";
import { OrbitControls } from "three/addons/controls/OrbitControls.js";
import { type Concept, type Edge, groups } from "@/lib/knowledge";
import { SceneControls, type SceneAction } from "./scene-controls";

type GraphNode = NodeObject<{ id: number; title: string; group: string }>;

export default function Graph3D({
  nodes,
  edges,
  selected,
  highlighted = [],
  onSelect,
  onFallback,
}: {
  nodes: Concept[];
  edges: Edge[];
  selected?: number;
  highlighted?: number[];
  onSelect: (id: number) => void;
  onFallback: () => void;
}) {
  const wrapper = useRef<HTMLDivElement>(null);
  const graph = useRef<ForceGraphMethods<GraphNode> | undefined>(undefined);
  const [size, setSize] = useState({ width: 600, height: 480 });
  const [settled, setSettled] = useState(false);
  const [paused, setPaused] = useState(false);
  const textures = useRef<CanvasTexture[]>([]);
  const materials = useRef<SpriteMaterial[]>([]);
  const sprites = useRef(new Map<number, Sprite>());
  const data = useMemo(
    () => ({
      nodes: nodes.map((n, i) => {
        const angle = i * 2.39996323;
        const y = 1 - (2 * (i + 0.5)) / nodes.length;
        const r = Math.sqrt(1 - y * y);
        const degree = edges.filter((e) => e.source === n.id || e.target === n.id).length;
        const radius = 115 + 25 * ((i * 0.381966) % 1);
        const x = Math.cos(angle) * r * radius;
        const py = y * radius;
        const z = Math.sin(angle) * r * radius;
        return {
          id: n.id,
          title: n.title,
          group: n.group,
          degree,
          x,
          y: py,
          z,
          fx: x,
          fy: py,
          fz: z,
        };
      }),
      links: edges.map((e) => ({ source: e.source, target: e.target })),
    }),
    [nodes, edges],
  );
  const fit = useCallback(() => {
    const api = graph.current;
    if (!api) return;
    const camera = api.camera() as PerspectiveCamera;
    const vertical = (camera.fov * Math.PI) / 360;
    const horizontal = Math.atan((Math.tan(vertical) * size.width) / Math.max(size.height, 1));
    const distance = (155 / Math.sin(Math.min(vertical, horizontal))) * 1.15;
    api.cameraPosition({ x: 0, y: 0, z: distance }, { x: 0, y: 0, z: 0 }, 0);
  }, [size]);
  const visibleLabelIds = useMemo(() => {
    const ids = new Set<number>();
    const neighbors = new Set(
      edges
        .filter((e) => e.source === selected || e.target === selected)
        .flatMap((e) => [e.source, e.target]),
    );
    nodes.forEach((node, index) => {
      if (
        index % 4 === 0 ||
        neighbors.has(node.id) ||
        node.id === selected ||
        highlighted.includes(node.id)
      )
        ids.add(node.id);
    });
    return ids;
  }, [nodes, edges, selected, highlighted]);

  useEffect(() => {
    const element = wrapper.current;
    if (!element) return;
    const observer = new ResizeObserver(([entry]) => {
      if (entry) setSize({ width: entry.contentRect.width, height: entry.contentRect.height });
    });
    observer.observe(element);
    const canvas = document.createElement("canvas");
    const probe = canvas.getContext("webgl2");
    if (!probe) onFallback();
    probe?.getExtension("WEBGL_lose_context")?.loseContext();
    const lost = (event: Event) => {
      event.preventDefault();
      onFallback();
    };
    element.addEventListener("webglcontextlost", lost, true);
    return () => {
      observer.disconnect();
      element.removeEventListener("webglcontextlost", lost, true);
    };
  }, [onFallback]);
  useEffect(() => {
    if (selected === undefined) fit();
  }, [fit, selected]);

  useEffect(
    () => () => {
      textures.current.forEach((t) => t.dispose());
      materials.current.forEach((m) => m.dispose());
      textures.current = [];
      materials.current = [];
      sprites.current.clear();
    },
    [],
  );

  const nodeObject = useCallback(
    (node: GraphNode) => {
      const cached = sprites.current.get(node.id);
      if (cached) {
        cached.visible = visibleLabelIds.has(node.id);
        return cached;
      }
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d")!;
      ctx.font = "500 32px sans-serif";
      canvas.width = Math.ceil(ctx.measureText(node.title).width) + 24;
      canvas.height = 56;
      ctx.font = "500 32px sans-serif";
      ctx.fillStyle = "rgba(255,255,255,.94)";
      ctx.beginPath();
      ctx.roundRect(0, 0, canvas.width, canvas.height, 15);
      ctx.fill();
      ctx.fillStyle = "#233f46";
      ctx.textAlign = "center";
      ctx.fillText(node.title, canvas.width / 2, 38);
      const texture = new CanvasTexture(canvas);
      texture.colorSpace = SRGBColorSpace;
      const material = new SpriteMaterial({ map: texture, depthWrite: false });
      textures.current.push(texture);
      materials.current.push(material);
      const sprite = new Sprite(material);
      const height = window.innerWidth < 700 ? 15 : 11;
      sprite.scale.set((canvas.width / 56) * height, height, 1);
      sprite.position.y = 8;
      sprite.visible = visibleLabelIds.has(node.id);
      sprites.current.set(node.id, sprite);
      return sprite;
    },
    [visibleLabelIds],
  );

  useEffect(() => {
    const node = data.nodes.find((n) => n.id === selected) as GraphNode | undefined;
    if (node && node.x !== undefined) {
      const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
      graph.current?.cameraPosition(
        { x: (node.x ?? 0) + 40, y: (node.y ?? 0) + 20, z: (node.z ?? 0) + 230 },
        { x: node.x ?? 0, y: node.y ?? 0, z: node.z ?? 0 },
        reduced ? 0 : 450,
      );
    }
  }, [selected, data]);

  useEffect(() => {
    const media = window.matchMedia("(prefers-reduced-motion: reduce)");
    const apply = () => {
      const controls = graph.current?.controls() as OrbitControls | undefined;
      if (controls) {
        controls.autoRotate = !paused && !media.matches;
        controls.autoRotateSpeed = 0.35;
      }
    };
    apply();
    media.addEventListener("change", apply);
    return () => media.removeEventListener("change", apply);
  }, [paused, settled]);

  const action = (value: SceneAction) => {
    const api = graph.current;
    if (!api) return;
    if (value === "reset") {
      fit();
      return;
    }
    const controls = api.controls() as OrbitControls;
    const camera = api.camera();
    const offset = new Vector3().copy(camera.position).sub(controls.target);
    if (value === "in" || value === "out") offset.multiplyScalar(value === "in" ? 0.85 : 1.17);
    else {
      const spherical = new Spherical().setFromVector3(offset);
      if (value === "left") spherical.theta -= 0.25;
      if (value === "right") spherical.theta += 0.25;
      if (value === "up") spherical.phi -= 0.2;
      if (value === "down") spherical.phi += 0.2;
      spherical.makeSafe();
      offset.setFromSpherical(spherical);
    }
    camera.position.copy(controls.target).add(offset);
    controls.update();
  };

  return (
    <div
      ref={wrapper}
      className="graph-three"
      data-ready={settled}
      aria-label="Peta pengetahuan 3D. Gunakan daftar konsep untuk navigasi keyboard."
    >
      <ForceGraph3D
        ref={graph}
        graphData={data}
        controlType="orbit"
        width={size.width}
        height={size.height}
        backgroundColor="#f7f8f6"
        showNavInfo={false}
        nodeLabel={(node) =>
          node.title.replace(
            /[&<>"']/g,
            (character) =>
              ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[character]!,
          )
        }
        nodeRelSize={3.8}
        nodeColor={(n) =>
          selected === n.id
            ? "#145c4b"
            : highlighted.length && !highlighted.includes(n.id)
              ? "#bdcbc3"
              : (groups[n.group]?.color ?? "#167a68")
        }
        linkColor={(link) => {
          const source = typeof link.source === "object" ? link.source.id : link.source;
          const target = typeof link.target === "object" ? link.target.id : link.target;
          return source === selected || target === selected ? "#258b76" : "#adb9b5";
        }}
        linkOpacity={0.55}
        linkWidth={0.65}
        onNodeClick={(n) => onSelect(n.id)}
        cooldownTicks={1}
        enableNodeDrag={false}
        nodeThreeObjectExtend
        nodeThreeObject={nodeObject}
        onEngineStop={() => {
          fit();
          setSettled(true);
        }}
      />
      <SceneControls paused={paused} onPause={() => setPaused((p) => !p)} onAction={action} />
      {!settled && (
        <span className="graph-layout-status" role="status">
          Menata hubungan konsep…
        </span>
      )}
    </div>
  );
}
