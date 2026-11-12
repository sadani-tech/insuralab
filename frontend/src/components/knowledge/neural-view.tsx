"use client";

import { useEffect, useRef, useState } from "react";
import * as THREE from "three";
import { OrbitControls } from "three/addons/controls/OrbitControls.js";
import { type Concept, type Edge } from "@/lib/knowledge";
import { SceneControls, type SceneAction } from "./scene-controls";

// Procedural cortex texture is decoration. Only the larger markers represent records.
function cortex(u: number, v: number) {
  const theta = u * Math.PI * 2;
  const phi = Math.acos(2 * v - 1);
  const fold =
    1 +
    0.045 * Math.sin(theta * 19 + Math.sin(phi * 7) * 2) * Math.sin(phi * 15) +
    0.025 * Math.cos(theta * 31 - phi * 13);
  const x = Math.cos(theta) * Math.sin(phi);
  const y = Math.cos(phi);
  const z = Math.sin(theta) * Math.sin(phi);
  const cleft = 1 - 0.13 * Math.exp(-Math.pow(z * 14, 2));
  return new THREE.Vector3(
    x * 1.65 * fold,
    (y * 1.08 + 0.08 * Math.cos(x * 3) - 0.06 * x) * fold * cleft,
    z * 1.05 * fold,
  );
}

export default function NeuralView({
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
  const host = useRef<HTMLDivElement>(null);
  const action = useRef<(action: SceneAction) => void>(() => {});
  const markersRef = useRef<THREE.Mesh<THREE.SphereGeometry, THREE.MeshBasicMaterial>[]>([]);
  const selectRef = useRef(onSelect);
  const emphasis = useRef({ selected, highlighted });
  const pausedRef = useRef(false);
  const [paused, setPaused] = useState(false);
  const [ready, setReady] = useState(false);
  const [hovered, setHovered] = useState<string | null>(null);
  useEffect(() => {
    selectRef.current = onSelect;
  }, [onSelect]);
  useEffect(() => {
    pausedRef.current = paused;
  }, [paused]);
  useEffect(() => {
    emphasis.current = { selected, highlighted };
    for (const marker of markersRef.current) {
      const active = marker.userData.id === selected;
      const related = highlighted.includes(marker.userData.id);
      marker.material.color.set(
        active ? "#ffffff" : related ? "#ffc47e" : highlighted.length ? "#35636a" : "#72e0c5",
      );
      marker.scale.setScalar(active ? 1.7 : related ? 1.4 : 1);
    }
  }, [selected, highlighted]);

  useEffect(() => {
    const element = host.current;
    if (!element) return;
    let renderer: THREE.WebGLRenderer;
    try {
      renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    } catch {
      onFallback();
      return;
    }
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
    element.appendChild(renderer.domElement);
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(40, 1, 0.1, 40);
    camera.position.set(0, 0.1, 6.7);
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.enablePan = false;
    controls.minDistance = 3.5;
    controls.maxDistance = 11;
    controls.autoRotateSpeed = 0.32;
    const reduced = window.matchMedia("(prefers-reduced-motion: reduce)");
    const brain = new THREE.Group();
    brain.rotation.set(0.18, -0.3, -0.12);
    scene.add(brain);
    const positions: number[] = [],
      colors: number[] = [];
    const teal = new THREE.Color("#54d5d2"),
      rose = new THREE.Color("#ec6985");
    const count = window.innerWidth < 700 ? 6500 : 12500;
    for (let i = 0; i < count; i++) {
      const u = (i * 0.61803398875) % 1;
      const v = (i + 0.5) / count;
      const p = cortex(u, v);
      positions.push(p.x, p.y, p.z);
      const c = teal.clone().lerp(rose, THREE.MathUtils.smoothstep(p.x, -0.5, 1.1));
      // Deterministic variation avoids a flat solid surface.
      c.multiplyScalar(0.35 + 0.65 * ((Math.sin(i * 127.1) + 1) / 2));
      colors.push(c.r, c.g, c.b);
    }
    // Cerebellum and brain stem complete the silhouette without anatomical claims.
    for (let i = 0; i < count / 6; i++) {
      const a = i * 2.39996323;
      const y = 1 - (2 * i) / (count / 6);
      const r = Math.sqrt(1 - y * y);
      positions.push(
        -0.85 + Math.cos(a) * r * 0.63,
        -0.88 + y * 0.45,
        Math.sin(a) * r * 0.62 - 0.08,
      );
      colors.push(0.12, 0.42, 0.45);
    }
    for (let i = 0; i < 350; i++) {
      const a = i * 2.39996323,
        t = i / 350;
      positions.push(
        -0.38 + t * 0.2 + Math.cos(a) * 0.11,
        -1.1 - t * 0.65,
        Math.sin(a) * 0.14 - 0.1,
      );
      colors.push(0.12, 0.32, 0.35);
    }
    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute("position", new THREE.Float32BufferAttribute(positions, 3));
    geometry.setAttribute("color", new THREE.Float32BufferAttribute(colors, 3));
    const particles = new THREE.PointsMaterial({
      size: 0.013,
      vertexColors: true,
      transparent: true,
      opacity: 0.85,
      depthWrite: false,
      blending: THREE.AdditiveBlending,
    });
    brain.add(new THREE.Points(geometry, particles));
    const markerGeometry = new THREE.SphereGeometry(0.026, 12, 8);
    const markers = nodes.map((node, i) => {
      const p = cortex(
        (i * 0.61803398875) % 1,
        0.12 + 0.76 * ((i + 0.5) / nodes.length),
      ).multiplyScalar(1.025);
      const marker = new THREE.Mesh(
        markerGeometry,
        new THREE.MeshBasicMaterial({ color: "#72e0c5" }),
      );
      marker.position.copy(p);
      marker.userData = { id: node.id, title: node.title };
      const active = node.id === emphasis.current.selected;
      const related = emphasis.current.highlighted.includes(node.id);
      marker.material.color.set(
        active
          ? "#ffffff"
          : related
            ? "#ffc47e"
            : emphasis.current.highlighted.length
              ? "#35636a"
              : "#72e0c5",
      );
      marker.scale.setScalar(active ? 1.7 : related ? 1.4 : 1);
      brain.add(marker);
      return marker;
    });
    markersRef.current = markers;
    const byId = new Map(markers.map((m) => [m.userData.id, m.position]));
    const lines: number[] = [];
    for (const edge of edges) {
      const a = byId.get(edge.source),
        b = byId.get(edge.target);
      if (a && b) lines.push(a.x, a.y, a.z, b.x, b.y, b.z);
    }
    const lineGeometry = new THREE.BufferGeometry();
    lineGeometry.setAttribute("position", new THREE.Float32BufferAttribute(lines, 3));
    const lineMaterial = new THREE.LineBasicMaterial({
      color: "#3ea5a2",
      transparent: true,
      opacity: 0.07,
      depthWrite: false,
    });
    brain.add(new THREE.LineSegments(lineGeometry, lineMaterial));
    const resize = new ResizeObserver(([entry]) => {
      if (!entry) return;
      const { width, height } = entry.contentRect;
      camera.aspect = width / Math.max(height, 1);
      // Fit the broad cortex in portrait canvases as well as wide ones.
      camera.fov = width < height * 0.9 ? 52 : 40;
      camera.updateProjectionMatrix();
      renderer.setSize(width, height);
    });
    resize.observe(element);
    action.current = (value) => {
      if (value === "reset") {
        camera.position.set(0, 0.1, 6.7);
        controls.target.set(0, 0, 0);
      } else if (value === "in" || value === "out") {
        const factor = value === "in" ? 0.85 : 1.17;
        camera.position
          .multiplyScalar(factor)
          .clampLength(controls.minDistance, controls.maxDistance);
      } else {
        const spherical = new THREE.Spherical().setFromVector3(camera.position);
        if (value === "left") spherical.theta -= 0.25;
        if (value === "right") spherical.theta += 0.25;
        if (value === "up") spherical.phi -= 0.2;
        if (value === "down") spherical.phi += 0.2;
        spherical.makeSafe();
        camera.position.setFromSpherical(spherical);
      }
      controls.update();
    };
    const raycaster = new THREE.Raycaster(),
      pointer = new THREE.Vector2();
    const hit = (event: PointerEvent) => {
      const rect = renderer.domElement.getBoundingClientRect();
      pointer.set(
        ((event.clientX - rect.left) / rect.width) * 2 - 1,
        (-(event.clientY - rect.top) / rect.height) * 2 + 1,
      );
      raycaster.setFromCamera(pointer, camera);
      return raycaster.intersectObjects(markers)[0]?.object;
    };
    let downX = 0,
      downY = 0;
    const down = (event: PointerEvent) => {
      downX = event.clientX;
      downY = event.clientY;
    };
    const click = (event: PointerEvent) => {
      if (Math.hypot(event.clientX - downX, event.clientY - downY) > 6) return;
      const marker = hit(event);
      if (marker) selectRef.current(marker.userData.id);
    };
    const move = (event: PointerEvent) => {
      const marker = hit(event);
      setHovered(marker?.userData.title ?? null);
      renderer.domElement.style.cursor = marker ? "pointer" : "grab";
    };
    const leave = () => setHovered(null);
    const lost = (event: Event) => {
      event.preventDefault();
      onFallback();
    };
    renderer.domElement.addEventListener("pointerdown", down);
    renderer.domElement.addEventListener("pointerup", click);
    renderer.domElement.addEventListener("pointermove", move);
    renderer.domElement.addEventListener("pointerleave", leave);
    renderer.domElement.addEventListener("webglcontextlost", lost);
    let frame = 0;
    let firstFrame = true;
    const render = () => {
      frame = requestAnimationFrame(render);
      controls.autoRotate = !pausedRef.current && !reduced.matches && !document.hidden;
      controls.update();
      renderer.render(scene, camera);
      if (firstFrame) {
        setReady(true);
        firstFrame = false;
      }
    };
    frame = requestAnimationFrame(render);
    return () => {
      cancelAnimationFrame(frame);
      resize.disconnect();
      controls.dispose();
      renderer.domElement.removeEventListener("pointerdown", down);
      renderer.domElement.removeEventListener("pointerup", click);
      renderer.domElement.removeEventListener("pointermove", move);
      renderer.domElement.removeEventListener("pointerleave", leave);
      renderer.domElement.removeEventListener("webglcontextlost", lost);
      geometry.dispose();
      particles.dispose();
      markerGeometry.dispose();
      markers.forEach((m) => m.material.dispose());
      lineGeometry.dispose();
      lineMaterial.dispose();
      renderer.dispose();
      renderer.forceContextLoss();
      renderer.domElement.remove();
      markersRef.current = [];
    };
  }, [nodes, edges, onFallback]);

  return (
    <div className="neural-scene" data-ready={ready}>
      <div
        ref={host}
        className="neural-canvas"
        role="img"
        aria-label="Neural View: visualisasi otak partikel dengan penanda konsep. Gunakan Daftar untuk memilih konsep dengan keyboard."
      />
      {hovered && <span className="neural-tooltip">{hovered}</span>}
      <SceneControls
        paused={paused}
        onPause={() => setPaused((p) => !p)}
        onAction={(value) => action.current(value)}
      />
    </div>
  );
}
