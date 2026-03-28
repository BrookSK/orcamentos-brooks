// ══════════════════════════════════════════════
//  CALCULADORA SINAPI - INTEGRADA AO SISTEMA
// ══════════════════════════════════════════════

// Carregar preços do arquivo de dados
const PRECOS_SINAPI = window.SINAPI_DATA?.precos || {};

// Estado global
let elementoAtualSINAPI = null;
let ultimoResultadoSINAPI = null;

// ══════════════════════════════════════════════
//  ELEMENTOS CONSTRUTIVOS
// ══════════════════════════════════════════════
const ELEMENTOS = [
  // ─── MUROS E PAREDES ───
  {
    id: 'muro_tijolo_furado',
    icon: '🧱', nome: 'Muro de tijolo cerâmico furado', cat: 'Alvenaria',
    desc: 'Muro externo com tijolo furado 9×14×19cm (e=14cm), chapisco + reboco, sem fundação',
    dims: [
      { id:'comp', label:'Comprimento', unit:'m', hint:'largura total do muro', default:10 },
      { id:'alt',  label:'Altura',      unit:'m', hint:'do piso até o topo',   default:2 },
    ],
    extras: [
      { id:'com_reboco', label:'Incluir chapisco + reboco (ambos os lados)', type:'check', default:true },
      { id:'com_pintura', label:'Incluir pintura látex (2 demãos)', type:'check', default:false },
      { id:'tipo_tijolo', label:'Tipo de tijolo', type:'select',
        options:[
          {val:'furado_14', label:'Furado 14cm (parede normal)'},
          {val:'furado_9',  label:'Furado 9cm (meia vez / divisória)'},
          {val:'macico_20', label:'Maciço 20cm (muro externo resistente)'},
          {val:'bloco_14',  label:'Bloco de concreto 14cm'},
        ], default:'furado_14' },
    ],
    calcFn(d) {
      const area = d.comp * d.alt;
      const variante = d.tipo_tijolo || 'furado_14';
      const mats = [];

      if (variante === 'furado_14') {
        mats.push({ key:'tijolo_furado_14', codigo_sinapi:'37593', nome:'Tijolo cerâmico furado 14×19×39cm', qty: area*25*1.05, tipo:'material' });
        mats.push({ key:'cimento_cpii', codigo_sinapi:'1379', nome:'Cimento Portland CP II-E 32', qty: area*3.84, tipo:'material' });
        mats.push({ key:'cal_hidratada', codigo_sinapi:'1106', nome:'Cal hidratada CH I', qty: area*2.04, tipo:'material' });
        mats.push({ key:'areia_media', codigo_sinapi:'370', nome:'Areia média lavada', qty: area*0.016, tipo:'material' });
      } else if (variante === 'furado_9') {
        mats.push({ key:'tijolo_furado_9', codigo_sinapi:'37592', nome:'Tijolo cerâmico furado 9×19×39cm', qty: area*33*1.05, tipo:'material' });
        mats.push({ key:'cimento_cpii', codigo_sinapi:'1379', nome:'Cimento Portland CP II-E 32', qty: area*2.52, tipo:'material' });
        mats.push({ key:'cal_hidratada', codigo_sinapi:'1106', nome:'Cal hidratada CH I', qty: area*1.34, tipo:'material' });
        mats.push({ key:'areia_media', codigo_sinapi:'370', nome:'Areia média lavada', qty: area*0.011, tipo:'material' });
      } else if (variante === 'macico_20') {
        mats.push({ key:'tijolo_macico', codigo_sinapi:'7258', nome:'Tijolo maciço cerâmico 5×10×20cm', qty: area*75*1.05, tipo:'material' });
        mats.push({ key:'cimento_cpii', codigo_sinapi:'1379', nome:'Cimento Portland CP II-E 32', qty: area*7.68, tipo:'material' });
        mats.push({ key:'areia_media', codigo_sinapi:'370', nome:'Areia média lavada', qty: area*0.030, tipo:'material' });
      } else if (variante === 'bloco_14') {
        mats.push({ key:'bloco_conc_14', codigo_sinapi:'25070', nome:'Bloco de concreto 14×19×39cm', qty: area*13*1.05, tipo:'material' });
        mats.push({ key:'cimento_cpii', codigo_sinapi:'1379', nome:'Cimento Portland CP II-E 32', qty: area*2.00, tipo:'material' });
        mats.push({ key:'areia_media', codigo_sinapi:'370', nome:'Areia média lavada', qty: area*0.008, tipo:'material' });
      }

      // Mão de obra assentamento
      mats.push({ key:'mo_pedreiro', codigo_sinapi:'88309', nome:'Pedreiro — assentamento', qty: area*0.62, tipo:'mao' });
      mats.push({ key:'mo_servente', codigo_sinapi:'88316', nome:'Servente — assentamento', qty: area*0.62, tipo:'mao' });

      if (d.com_reboco) {
        // Chapisco (ambos lados)
        mats.push({ key:'cimento_cpii', codigo_sinapi:'1379', nome:'Cimento — chapisco (2 faces)', qty: area*2*3.00, tipo:'material' });
        mats.push({ key:'areia_grossa', codigo_sinapi:'367', nome:'Areia grossa — chapisco', qty: area*2*0.009, tipo:'material' });
        // Emboço / reboco (2 lados)
        mats.push({ key:'cimento_cpii', codigo_sinapi:'1379', nome:'Cimento — reboco (2 faces)', qty: area*2*5.20, tipo:'material' });
        mats.push({ key:'cal_hidratada', codigo_sinapi:'1106', nome:'Cal hidratada — reboco', qty: area*2*1.80, tipo:'material' });
        mats.push({ key:'areia_media', codigo_sinapi:'370', nome:'Areia média — reboco', qty: area*2*0.022, tipo:'material' });
        mats.push({ key:'mo_pedreiro', codigo_sinapi:'88309', nome:'Pedreiro — chapisco+reboco', qty: area*2*0.70, tipo:'mao' });
        mats.push({ key:'mo_servente', codigo_sinapi:'88316', nome:'Servente — chapisco+reboco', qty: area*2*0.70, tipo:'mao' });
      }

      if (d.com_pintura) {
        mats.push({ key:'massa_corrida', codigo_sinapi:'38448', nome:'Massa corrida PVA', qty: area*2*0.30, tipo:'material' });
        mats.push({ key:'selador', codigo_sinapi:'6085', nome:'Selador acrílico', qty: area*2*0.10, tipo:'material' });
        mats.push({ key:'tinta_latex', codigo_sinapi:'35692', nome:'Tinta látex acrílica', qty: area*2*0.48, tipo:'material' });
        mats.push({ key:'mo_pintor', codigo_sinapi:'4783', nome:'Pintor — pintura', qty: area*2*0.35, tipo:'mao' });
        mats.push({ key:'mo_servente', codigo_sinapi:'88316', nome:'Servente — pintura', qty: area*2*0.10, tipo:'mao' });
      }

      // Betoneira
      mats.push({ key:'eq_betoneira', codigo_sinapi:'10535', nome:'Betoneira 400L', qty: area*0.05, tipo:'equip' });

      return { qty: area, un: 'm²', mats };
    }
  },
  // ─── LAJE ───
  {
    id: 'laje_macica',
    icon: '🏗️', nome: 'Laje maciça concreto armado', cat: 'Estrutura',
    desc: 'Laje maciça e=10cm, fck=20MPa, incluindo forma, armação e concretagem',
    dims: [
      { id:'comp', label:'Comprimento', unit:'m', hint:'comprimento do cômodo/ambiente', default:5 },
      { id:'larg', label:'Largura',     unit:'m', hint:'largura do cômodo/ambiente',     default:4 },
    ],
    extras: [
      { id:'tipo_laje', label:'Tipo de laje', type:'select',
        options:[
          {val:'macica_10',  label:'Maciça e=10cm (residencial leve)'},
          {val:'macica_12',  label:'Maciça e=12cm (residencial)'},
          {val:'premoldada', label:'Pré-moldada vigota+lajota (econômica)'},
        ], default:'macica_10' },
      { id:'concreto_usinado', label:'Usar concreto usinado (mais rápido)', type:'check', default:false },
    ],
    calcFn(d) {
      const area = d.comp * d.larg;
      const esp = d.tipo_laje === 'macica_12' ? 0.12 : 0.10;
      const vol = area * esp;
      const mats = [];

      if (d.tipo_laje === 'premoldada') {
        mats.push({ key:'bloco_conc_14',    nome:'Lajota EPS / cerâmica (enchimento)',  qty: area*8,      tipo:'material' });
        mats.push({ key:'aco_ca50_8',       nome:'Vigota pré-moldada (≈estimativa em kg ferragem)', qty: area*1.50, tipo:'material' });
        mats.push({ key:'cimento_cpii',     nome:'Cimento — capa de concreto 4cm',      qty: area*8.50,   tipo:'material' });
        mats.push({ key:'areia_media',      nome:'Areia média — capa',                   qty: area*0.022,  tipo:'material' });
        mats.push({ key:'brita_1',          nome:'Brita 1 — capa',                       qty: area*0.024,  tipo:'material' });
        mats.push({ key:'aco_ca60_5',       nome:'Fio CA-60 ø4,2mm (capa)',              qty: area*1.50,   tipo:'material' });
        mats.push({ key:'pontalete_3x3',    nome:'Pontalete pinus 7×7cm (escoramento)',  qty: area*1.20,   tipo:'material' });
        mats.push({ key:'mo_pedreiro',      nome:'Pedreiro — montagem e concretagem',    qty: area*1.00,   tipo:'mao' });
        mats.push({ key:'mo_servente',      nome:'Servente',                              qty: area*1.50,   tipo:'mao' });
        mats.push({ key:'mo_carpinteiro',   nome:'Carpinteiro — escoramento',             qty: area*0.50,   tipo:'mao' });
      } else {
        // Forma
        mats.push({ key:'compens_10mm',    nome:'Compensado resinado 10mm (forma)',     qty: area*1.05,   tipo:'material' });
        mats.push({ key:'pontalete_3x3',   nome:'Pontalete pinus 7×7cm (escoramento)', qty: area*1.50,   tipo:'material' });
        mats.push({ key:'prego_18x30',     nome:'Prego com cabeça 18×30',               qty: area*0.20,   tipo:'material' });
        // Armação
        const kg_aco = esp === 0.12 ? 9.00 : 7.00;
        mats.push({ key:'aco_ca50_8',      nome:'Aço CA-50 ø8,0mm (armação principal)', qty: area*kg_aco, tipo:'material' });
        mats.push({ key:'aco_ca60_5',      nome:'Fio CA-60 (distribuição)',              qty: area*2.00,   tipo:'material' });
        // Concreto
        if (d.concreto_usinado) {
          mats.push({ key:'concreto_20mpa', nome:'Concreto usinado fck=20MPa',           qty: vol*1.05,    tipo:'material' });
        } else {
          mats.push({ key:'cimento_cpii',   nome:'Cimento Portland CP II-E 32',          qty: vol*355,     tipo:'material' });
          mats.push({ key:'areia_media',    nome:'Areia média lavada',                    qty: vol*0.68,    tipo:'material' });
          mats.push({ key:'brita_1',        nome:'Brita 1 (9,5–19mm)',                   qty: vol*0.77,    tipo:'material' });
          mats.push({ key:'eq_betoneira',   nome:'Betoneira 400L',                        qty: vol*2.50,    tipo:'equip' });
        }
        mats.push({ key:'eq_vibrador',      nome:'Vibrador de imersão',                  qty: vol*1.50,    tipo:'equip' });
        mats.push({ key:'mo_pedreiro',      nome:'Pedreiro — concretagem',               qty: area*2.20,   tipo:'mao' });
        mats.push({ key:'mo_servente',      nome:'Servente',                              qty: area*3.30,   tipo:'mao' });
        mats.push({ key:'mo_carpinteiro',   nome:'Carpinteiro — forma',                  qty: area*0.80,   tipo:'mao' });
        mats.push({ key:'mo_armador',       nome:'Armador — ferragem',                   qty: area*0.60,   tipo:'mao' });
      }

      return { qty: area, un: 'm²', mats };
    }
  },
  // ─── PISO ───
  {
    id: 'piso_ceramico',
    icon: '🟫', nome: 'Piso cerâmico / porcelanato', cat: 'Pisos',
    desc: 'Assentamento sobre contrapiso existente, argamassa AC III, rejunte e arremate',
    dims: [
      { id:'comp', label:'Comprimento', unit:'m', default:6 },
      { id:'larg', label:'Largura',     unit:'m', default:4 },
    ],
    extras: [
      { id:'com_contrapiso', label:'Incluir contrapiso 5cm', type:'check', default:true },
      { id:'tipo_piso', label:'Tipo de piso', type:'select',
        options:[
          {val:'ceramico',    label:'Cerâmico (30×30 a 60×60cm)'},
          {val:'porcelanato', label:'Porcelanato polido (60×60cm+)'},
        ], default:'ceramico' },
    ],
    calcFn(d) {
      const area = d.comp * d.larg;
      const mats = [];
      const preco_piso = d.tipo_piso === 'porcelanato' ? 58.00 : 38.00;

      if (d.com_contrapiso) {
        mats.push({ key:'cimento_cpii',   nome:'Cimento — contrapiso 5cm',  qty: area*8.33,  tipo:'material' });
        mats.push({ key:'areia_grossa',   nome:'Areia grossa — contrapiso', qty: area*0.040, tipo:'material' });
        mats.push({ key:'mo_pedreiro',    nome:'Pedreiro — contrapiso',     qty: area*0.40,  tipo:'mao' });
        mats.push({ key:'mo_servente',    nome:'Servente — contrapiso',     qty: area*0.80,  tipo:'mao' });
      }

      // Assentamento
      mats.push({
        key: d.tipo_piso === 'porcelanato' ? 'piso_ceramico' : 'piso_ceramico',
        nome: d.tipo_piso === 'porcelanato' ? 'Porcelanato polido (m²)' : 'Piso cerâmico (m²)',
        qty: area*1.05, tipo:'material',
        precoOverride: preco_piso
      });
      mats.push({ key:'arg_colante_ac2',  nome:'Argamassa colante AC III',  qty: area*5.50,  tipo:'material' });
      mats.push({ key:'rejunte_normal',   nome:'Rejunte flexível',           qty: area*0.50,  tipo:'material' });
      mats.push({ key:'mo_pedreiro',      nome:'Pedreiro — assentamento',   qty: area*0.75,  tipo:'mao' });
      mats.push({ key:'mo_servente',      nome:'Servente',                   qty: area*0.37,  tipo:'mao' });
      mats.push({ key:'eq_betoneira',     nome:'Betoneira (contrapiso)',     qty: area*0.08,  tipo:'equip' });

      return { qty: area, un: 'm²', mats };
    }
  },
  // ─── REBOCO / REVESTIMENTO ───
  {
    id: 'revestimento_parede',
    icon: '🪣', nome: 'Revestimento de parede', cat: 'Revestimento',
    desc: 'Chapisco, emboço e reboco interno ou azulejo, com preparo de superfície',
    dims: [
      { id:'comp', label:'Comprimento total da parede', unit:'m', default:12 },
      { id:'alt',  label:'Altura',                       unit:'m', default:2.8 },
    ],
    extras: [
      { id:'tipo_rev', label:'Tipo de revestimento', type:'select',
        options:[
          {val:'reboco',  label:'Chapisco + reboco (argamassa)'},
          {val:'azulejo', label:'Chapisco + reboco + azulejo'},
          {val:'gesso',   label:'Chapisco + gesso projetado'},
        ], default:'reboco' },
      { id:'com_pintura', label:'Incluir pintura (só para reboco/gesso)', type:'check', default:false },
    ],
    calcFn(d) {
      const area = d.comp * d.alt;
      const mats = [];

      // Chapisco (sempre)
      mats.push({ key:'cimento_cpii', nome:'Cimento — chapisco',  qty: area*3.00,  tipo:'material' });
      mats.push({ key:'areia_grossa', nome:'Areia grossa — chapisco', qty: area*0.009, tipo:'material' });
      mats.push({ key:'mo_pedreiro',  nome:'Pedreiro — chapisco',  qty: area*0.20,  tipo:'mao' });
      mats.push({ key:'mo_servente',  nome:'Servente — chapisco',  qty: area*0.20,  tipo:'mao' });

      if (d.tipo_rev === 'reboco' || d.tipo_rev === 'azulejo') {
        mats.push({ key:'cimento_cpii',   nome:'Cimento — emboço/reboco',  qty: area*5.20,   tipo:'material' });
        mats.push({ key:'cal_hidratada',  nome:'Cal hidratada — reboco',    qty: area*1.80,   tipo:'material' });
        mats.push({ key:'areia_media',    nome:'Areia média — reboco',      qty: area*0.022,  tipo:'material' });
        mats.push({ key:'mo_pedreiro',    nome:'Pedreiro — reboco',         qty: area*0.50,   tipo:'mao' });
        mats.push({ key:'mo_servente',    nome:'Servente — reboco',         qty: area*0.50,   tipo:'mao' });
      }

      if (d.tipo_rev === 'gesso') {
        mats.push({ key:'arg_ind_reboco', nome:'Gesso em pó (saco 20kg)',   qty: area*8.00,   tipo:'material' });
        mats.push({ key:'mo_pedreiro',    nome:'Gesseiro',                   qty: area*0.25,   tipo:'mao' });
        mats.push({ key:'mo_servente',    nome:'Servente',                   qty: area*0.20,   tipo:'mao' });
      }

      if (d.tipo_rev === 'azulejo') {
        mats.push({ key:'azulejo',       nome:'Azulejo cerâmico',           qty: area*1.05,   tipo:'material' });
        mats.push({ key:'arg_colante_ac2',nome:'Argamassa colante AC II',   qty: area*4.50,   tipo:'material' });
        mats.push({ key:'rejunte_normal', nome:'Rejunte normal',             qty: area*0.35,   tipo:'material' });
        mats.push({ key:'mo_pedreiro',    nome:'Pedreiro — assentamento',   qty: area*0.70,   tipo:'mao' });
        mats.push({ key:'mo_servente',    nome:'Servente',                   qty: area*0.35,   tipo:'mao' });
      }

      if (d.com_pintura && d.tipo_rev !== 'azulejo') {
        mats.push({ key:'massa_corrida',  nome:'Massa corrida PVA',         qty: area*0.30,   tipo:'material' });
        mats.push({ key:'selador',        nome:'Selador acrílico',           qty: area*0.10,   tipo:'material' });
        mats.push({ key:'tinta_latex',    nome:'Tinta látex acrílica',       qty: area*0.48,   tipo:'material' });
        mats.push({ key:'mo_pintor',      nome:'Pintor',                     qty: area*0.35,   tipo:'mao' });
        mats.push({ key:'mo_servente',    nome:'Servente',                   qty: area*0.10,   tipo:'mao' });
      }

      mats.push({ key:'eq_betoneira',    nome:'Betoneira 400L',             qty: area*0.04,   tipo:'equip' });

      return { qty: area, un: 'm²', mats };
    }
  },
  // ─── TELHADO ───
  {
    id: 'telhado',
    icon: '🏠', nome: 'Telhado', cat: 'Cobertura',
    desc: 'Estrutura de madeira + telhas, para coberturas residenciais',
    dims: [
      { id:'comp', label:'Comprimento', unit:'m', default:8, hint:'projeção horizontal' },
      { id:'larg', label:'Largura',     unit:'m', default:6, hint:'projeção horizontal' },
      { id:'incl', label:'Inclinação',  unit:'%', default:30, hint:'ex: 30% para 2 águas' },
    ],
    extras: [
      { id:'tipo_telha', label:'Tipo de telha', type:'select',
        options:[
          {val:'ceramica',    label:'Cerâmica capa-canal'},
          {val:'fibrocimento',label:'Fibrocimento ondulada 6mm'},
          {val:'metalica',    label:'Metálica trapezoidal'},
        ], default:'ceramica' },
      { id:'com_forro', label:'Incluir forro de PVC', type:'check', default:false },
    ],
    calcFn(d) {
      const area_proj = d.comp * d.larg;
      const fator_inc = 1 + (d.incl / 100) * 0.5;
      const area_real = area_proj * fator_inc;
      const mats = [];

      // Estrutura de madeira (comum a todos)
      mats.push({ key:'tercas_pinus',  nome:'Terça pinus 5×10cm',          qty: area_real*0.60, tipo:'material' });
      mats.push({ key:'caibro_pinus',  nome:'Caibro pinus 5×5cm',           qty: area_real*1.20, tipo:'material' });
      mats.push({ key:'ripa_pinus',    nome:'Ripa pinus 2,5×5cm',           qty: area_real*2.50, tipo:'material' });
      mats.push({ key:'prego_18x30',   nome:'Prego com cabeça 18×30',       qty: area_real*0.15, tipo:'material' });
      mats.push({ key:'mo_carpinteiro',nome:'Carpinteiro — estrutura madeira', qty: area_real*0.55, tipo:'mao' });
      mats.push({ key:'mo_servente',   nome:'Servente',                      qty: area_real*0.55, tipo:'mao' });

      if (d.tipo_telha === 'ceramica') {
        mats.push({ key:'telha_ceramica', nome:'Telha cerâmica capa-canal', qty: area_real*16*1.05, tipo:'material' });
      } else if (d.tipo_telha === 'fibrocimento') {
        mats.push({ key:'telha_fibrocim', nome:'Telha fibrocimento ondulada 6mm', qty: area_real*1.12, tipo:'material' });
      } else {
        mats.push({
          key:'telha_ceramica', nome:'Telha metálica trapezoidal galv.',
          qty: area_real*1.10, tipo:'material',
          precoOverride: 68.00
        });
      }

      if (d.com_forro) {
        mats.push({
          key:'piso_ceramico', nome:'Régua de PVC p/ forro',
          qty: area_proj*1.08, tipo:'material',
          precoOverride: 22.00
        });
        mats.push({ key:'mo_carpinteiro', nome:'Carpinteiro — forro',       qty: area_proj*0.40, tipo:'mao' });
        mats.push({ key:'mo_servente',    nome:'Servente — forro',           qty: area_proj*0.20, tipo:'mao' });
      }

      mats.push({ key:'eq_andaime',    nome:'Andaime tubular',               qty: area_real*0.20, tipo:'equip' });

      return { qty: area_real, un: 'm²', mats, nota: `Área projetada: ${fmt(area_proj,2)} m² · Área real de telhado: ${fmt(area_real,2)} m²` };
    }
  },
  // ─── FUNDAÇÃO CORRIDA ───
  {
    id: 'fundacao_corrida',
    icon: '⛏️', nome: 'Fundação corrida de concreto', cat: 'Fundação',
    desc: 'Baldrame / sapata corrida em concreto simples, para muros e construções simples',
    dims: [
      { id:'comp', label:'Comprimento total', unit:'m', default:20, hint:'soma de todos os trechos' },
      { id:'larg', label:'Largura',           unit:'m', default:0.4, hint:'ex: 0,40m' },
      { id:'alt',  label:'Altura / Profund.', unit:'m', default:0.6, hint:'profundidade mínima 60cm' },
    ],
    extras: [
      { id:'tipo_fund', label:'Tipo', type:'select',
        options:[
          {val:'conc_simples', label:'Concreto simples fck=15MPa'},
          {val:'conc_armado',  label:'Concreto armado fck=20MPa'},
        ], default:'conc_simples' },
      { id:'com_escav', label:'Incluir escavação manual', type:'check', default:true },
    ],
    calcFn(d) {
      const vol = d.comp * d.larg * d.alt;
      const mats = [];

      if (d.com_escav) {
        mats.push({ key:'mo_servente', nome:'Servente — escavação manual', qty: vol*3.30, tipo:'mao' });
      }

      if (d.tipo_fund === 'conc_simples') {
        mats.push({ key:'cimento_cpii', nome:'Cimento Portland CP II-E 32', qty: vol*210, tipo:'material' });
        mats.push({ key:'areia_media',  nome:'Areia média lavada',           qty: vol*0.56, tipo:'material' });
        mats.push({ key:'brita_1',      nome:'Brita 1',                      qty: vol*0.61, tipo:'material' });
      } else {
        mats.push({ key:'cimento_cpii', nome:'Cimento Portland CP II-E 32', qty: vol*320, tipo:'material' });
        mats.push({ key:'areia_media',  nome:'Areia média lavada',           qty: vol*0.62, tipo:'material' });
        mats.push({ key:'brita_1',      nome:'Brita 1',                      qty: vol*0.72, tipo:'material' });
        mats.push({ key:'aco_ca50_8',   nome:'Aço CA-50 ø8,0mm',            qty: vol*40,   tipo:'material' });
        mats.push({ key:'tabua_pinus_form',nome:'Tábua pinus — forma lateral', qty: d.comp*2*d.alt*0.90, tipo:'material' });
        mats.push({ key:'mo_armador',   nome:'Armador',                      qty: vol*5.00, tipo:'mao' });
      }

      mats.push({ key:'mo_pedreiro',  nome:'Pedreiro — fundação',           qty: vol*4.50, tipo:'mao' });
      mats.push({ key:'mo_servente',  nome:'Servente',                       qty: vol*9.00, tipo:'mao' });
      mats.push({ key:'eq_betoneira', nome:'Betoneira 400L',                 qty: vol*2.00, tipo:'equip' });

      return { qty: vol, un: 'm³', mats };
    }
  },
  // ─── CONTRAPISO ───
  {
    id: 'contrapiso',
    icon: '🟩', nome: 'Contrapiso (regularização)', cat: 'Pisos',
    desc: 'Regularização em argamassa traço 1:4, espessura 5cm, desempenado',
    dims: [
      { id:'comp', label:'Comprimento', unit:'m', default:8 },
      { id:'larg', label:'Largura',     unit:'m', default:5 },
    ],
    extras: [
      { id:'esp', label:'Espessura', type:'select',
        options:[
          {val:'4', label:'4 cm'},
          {val:'5', label:'5 cm'},
          {val:'7', label:'7 cm'},
        ], default:'5' },
    ],
    calcFn(d) {
      const area = d.comp * d.larg;
      const esp = parseFloat(d.esp || 5) / 100;
      const vol = area * esp;
      const mats = [];
      mats.push({ key:'cimento_cpii', nome:'Cimento Portland CP II-E 32', qty: vol*333,   tipo:'material' });
      mats.push({ key:'areia_grossa', nome:'Areia grossa lavada',          qty: vol*0.80,  tipo:'material' });
      mats.push({ key:'mo_pedreiro',  nome:'Pedreiro — contrapiso',        qty: area*0.40, tipo:'mao' });
      mats.push({ key:'mo_servente',  nome:'Servente',                      qty: area*0.80, tipo:'mao' });
      mats.push({ key:'eq_betoneira', nome:'Betoneira 400L',                qty: vol*2.00,  tipo:'equip' });
      return { qty: area, un: 'm²', mats };
    }
  },
  // ─── PINTURA ───
  {
    id: 'pintura',
    icon: '🎨', nome: 'Pintura', cat: 'Pintura',
    desc: 'Paredes e teto — massa corrida, selador e tinta látex/acrílica',
    dims: [
      { id:'comp', label:'Comprimento total de parede', unit:'m', default:24 },
      { id:'alt',  label:'Altura (parede)',              unit:'m', default:2.8 },
    ],
    extras: [
      { id:'com_teto', label:'Incluir teto (mesma área de planta)', type:'check', default:false },
      { id:'area_teto_comp', label:'Comprimento do teto (m)', type:'input_cond', condition:'com_teto', default:6 },
      { id:'area_teto_larg', label:'Largura do teto (m)',     type:'input_cond', condition:'com_teto', default:5 },
      { id:'tipo_tinta', label:'Tipo de tinta', type:'select',
        options:[
          {val:'latex_pva',  label:'Látex PVA (econômica)'},
          {val:'acrilica',   label:'Látex acrílica (melhor qualidade)'},
          {val:'textura',    label:'Textura acrílica rústica'},
        ], default:'acrilica' },
    ],
    calcFn(d) {
      let area = d.comp * d.alt;
      if (d.com_teto) {
        area += parseFloat(d.area_teto_comp||6) * parseFloat(d.area_teto_larg||5);
      }
      const mats = [];
      mats.push({ key:'massa_corrida', nome:'Massa corrida PVA',     qty: area*0.30, tipo:'material' });
      mats.push({ key:'selador',       nome:'Selador acrílico',       qty: area*0.10, tipo:'material' });
      if (d.tipo_tinta === 'latex_pva') {
        mats.push({ key:'tinta_latex', nome:'Tinta látex PVA (2 demãos)', qty: area*0.44, tipo:'material', precoOverride:8.50 });
      } else if (d.tipo_tinta === 'acrilica') {
        mats.push({ key:'tinta_latex', nome:'Tinta látex acrílica (2 demãos)', qty: area*0.48, tipo:'material' });
      } else {
        mats.push({ key:'tinta_latex', nome:'Massa textura acrílica (2kg/m²)', qty: area*1.80, tipo:'material', precoOverride:5.20 });
        mats.push({ key:'tinta_latex', nome:'Tinta acabamento textura',         qty: area*0.30, tipo:'material', precoOverride:12.50 });
      }
      mats.push({ key:'mo_pintor',   nome:'Pintor (2 demãos)',  qty: area*0.35, tipo:'mao' });
      mats.push({ key:'mo_servente', nome:'Servente — pintura', qty: area*0.10, tipo:'mao' });
      return { qty: area, un: 'm²', mats };
    }
  },
  // ─── IMPERMEABILIZAÇÃO ───
  {
    id: 'impermeabilizacao',
    icon: '💧', nome: 'Impermeabilização', cat: 'Impermeab.',
    desc: 'Manta asfáltica, argamassa cristalizante ou emulsão betuminosa',
    dims: [
      { id:'comp', label:'Comprimento', unit:'m', default:5 },
      { id:'larg', label:'Largura',     unit:'m', default:4 },
    ],
    extras: [
      { id:'tipo_imp', label:'Sistema', type:'select',
        options:[
          {val:'manta',       label:'Manta asfáltica APP 3mm'},
          {val:'cristaliz',   label:'Argamassa cristalizante bicomp.'},
          {val:'emulsao',     label:'Emulsão betuminosa + geotêxtil'},
        ], default:'manta' },
    ],
    calcFn(d) {
      const area = d.comp * d.larg;
      const mats = [];
      if (d.tipo_imp === 'manta') {
        mats.push({ key:'manta_asf_3mm', nome:'Manta asfáltica APP 3mm',    qty: area*1.15, tipo:'material' });
        mats.push({ key:'primer_asf',    nome:'Primer asfáltico base solv.', qty: area*0.30, tipo:'material' });
        mats.push({ key:'mo_pedreiro',   nome:'Impermeabilizador',            qty: area*0.55, tipo:'mao' });
        mats.push({ key:'mo_servente',   nome:'Servente',                     qty: area*0.27, tipo:'mao' });
      } else if (d.tipo_imp === 'cristaliz') {
        mats.push({ key:'arg_ind_assent', nome:'Argamassa impermeabilizante bicomp.', qty: area*4.00, tipo:'material', precoOverride:12.50 });
        mats.push({ key:'mo_pedreiro',    nome:'Aplicador',                    qty: area*0.45, tipo:'mao' });
        mats.push({ key:'mo_servente',    nome:'Servente',                      qty: area*0.22, tipo:'mao' });
      } else {
        mats.push({ key:'primer_asf',    nome:'Emulsão betuminosa',           qty: area*1.20, tipo:'material', precoOverride:11.00 });
        mats.push({ key:'manta_asf_3mm', nome:'Geotêxtil 200g/m²',           qty: area*1.10, tipo:'material', precoOverride:8.50 });
        mats.push({ key:'mo_pedreiro',   nome:'Aplicador',                    qty: area*0.30, tipo:'mao' });
        mats.push({ key:'mo_servente',   nome:'Servente',                     qty: area*0.20, tipo:'mao' });
      }
      return { qty: area, un: 'm²', mats };
    }
  },
  // ─── CALÇADA ───
  {
    id: 'calcada',
    icon: '🚶', nome: 'Calçada de concreto', cat: 'Pisos',
    desc: 'Piso externo de concreto simples e=8cm, desempenado, com juntas de dilatação',
    dims: [
      { id:'comp', label:'Comprimento', unit:'m', default:10 },
      { id:'larg', label:'Largura',     unit:'m', default:1.5 },
    ],
    extras: [
      { id:'tipo_calc', label:'Acabamento', type:'select',
        options:[
          {val:'desempenado', label:'Desempenado liso'},
          {val:'vassoura',    label:'Escovado (antiderrapante)'},
          {val:'pedrisco',    label:'Lavado com pedrisco'},
        ], default:'desempenado' },
    ],
    calcFn(d) {
      const area = d.comp * d.larg;
      const vol = area * 0.08;
      const mats = [];
      mats.push({ key:'cimento_cpii', nome:'Cimento Portland CP II-E 32', qty: vol*300,  tipo:'material' });
      mats.push({ key:'areia_media',  nome:'Areia média lavada',           qty: vol*0.72, tipo:'material' });
      mats.push({ key:'brita_1',      nome:'Brita 1',                      qty: vol*0.72, tipo:'material' });
      if (d.tipo_calc === 'pedrisco') {
        mats.push({ key:'brita_0', nome:'Pedrisco (brita 0) — cobertura', qty: area*0.008, tipo:'material' });
      }
      mats.push({ key:'mo_pedreiro',  nome:'Pedreiro — calçada',  qty: area*0.55, tipo:'mao' });
      mats.push({ key:'mo_servente',  nome:'Servente',             qty: area*1.10, tipo:'mao' });
      mats.push({ key:'eq_betoneira', nome:'Betoneira 400L',       qty: vol*1.50,  tipo:'equip' });
      return { qty: area, un: 'm²', mats };
    }
  },
];

// ══════════════════════════════════════════════
//  RENDER GRID
// ══════════════════════════════════════════════
function renderGridSINAPI() {
  const grid = document.getElementById('sinapi-element-grid');
  if (!grid) return;
  grid.innerHTML = ELEMENTOS.map(el => `
    <div class="element-card" id="ec_sinapi_${el.id}" onclick="selecionarElementoSINAPI('${el.id}')" style="cursor:pointer; padding:12px; border:1px solid rgba(255,255,255,.1); border-radius:8px; background:rgba(255,255,255,.02); text-align:center; transition:all .2s;">
      <div style="font-size:32px; margin-bottom:8px;">${el.icon}</div>
      <div style="font-size:12px; font-weight:700; margin-bottom:4px;">${el.nome}</div>
      <div style="font-size:10px; color:#999;">${el.cat}</div>
    </div>
  `).join('');
}

function selecionarElementoSINAPI(id) {
  elementoAtualSINAPI = ELEMENTOS.find(e => e.id === id);
  if (!elementoAtualSINAPI) return;
  
  document.querySelectorAll('.element-card').forEach(c => c.classList.remove('selected'));
  const card = document.getElementById('ec_sinapi_'+id);
  if (card) card.style.background = 'rgba(201,151,58,.15)';

  // Preencher step 2
  document.getElementById('sinapi-el-icon').textContent = elementoAtualSINAPI.icon;
  document.getElementById('sinapi-el-name').textContent = elementoAtualSINAPI.nome;
  document.getElementById('sinapi-el-desc').textContent = elementoAtualSINAPI.desc;

  // Campos de dimensão
  const df = document.getElementById('sinapi-dims-fields');
  df.innerHTML = elementoAtualSINAPI.dims.map(d => `
    <div style="margin-bottom:12px;">
      <label style="display:block; font-size:11px; color:#999; margin-bottom:4px;">${d.label} (${d.unit})</label>
      <div style="position:relative;">
        <input type="number" id="d_sinapi_${d.id}" value="${d.default || ''}" min="0" step="0.01"
               placeholder="0,00" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.04); color:#fff;">
        <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:11px; color:#999;">${d.unit}</span>
      </div>
      ${d.hint ? `<div style="font-size:10px; color:#666; margin-top:4px;">↳ ${d.hint}</div>` : ''}
    </div>
  `).join('');

  // Extras
  const ec = document.getElementById('sinapi-extras-container');
  if (elementoAtualSINAPI.extras && elementoAtualSINAPI.extras.length > 0) {
    const inputs = elementoAtualSINAPI.extras.map(ex => {
      if (ex.type === 'check') {
        return `<div style="margin-bottom:10px;"><label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
          <input type="checkbox" id="ex_sinapi_${ex.id}" ${ex.default ? 'checked' : ''} onchange="toggleConditionals()">
          <span style="font-size:12px;">${ex.label}</span>
        </label></div>`;
      } else if (ex.type === 'select') {
        return `<div style="margin-bottom:12px;">
          <label style="display:block; font-size:11px; color:#999; margin-bottom:4px;">${ex.label}</label>
          <select id="ex_sinapi_${ex.id}" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.04); color:#fff;">
            ${ex.options.map(o => `<option value="${o.val}" ${o.val===ex.default?'selected':''}>${o.label}</option>`).join('')}
          </select>
        </div>`;
      } else if (ex.type === 'input_cond') {
        return `<div id="cond_sinapi_${ex.id}" style="display:none; margin-bottom:12px;">
          <label style="display:block; font-size:11px; color:#999; margin-bottom:4px;">${ex.label}</label>
          <input type="number" id="ex_sinapi_${ex.id}" value="${ex.default||''}" step="0.01" min="0" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.04); color:#fff;">
        </div>`;
      }
      return '';
    }).join('');
    ec.innerHTML = `<div style="background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.1); border-radius:8px; padding:16px; margin-bottom:16px;">
      <div style="font-size:12px; font-weight:700; margin-bottom:12px; color:#C9973A;">Opções adicionais</div>
      ${inputs}
    </div>`;
    toggleConditionals();
  } else {
    ec.innerHTML = '';
  }

  document.getElementById('sinapi-step1').style.display = 'none';
  document.getElementById('sinapi-step2').style.display = 'block';
  document.getElementById('sinapi-resultado').style.display = 'none';
}

function toggleConditionals() {
  if (!elementoAtualSINAPI) return;
  elementoAtualSINAPI.extras?.filter(ex => ex.type === 'input_cond').forEach(ex => {
    const condEl = document.getElementById('ex_sinapi_'+ex.condition);
    const wrapper = document.getElementById('cond_sinapi_'+ex.id);
    if (wrapper && condEl) {
      wrapper.style.display = condEl.checked ? 'block' : 'none';
    }
  });
}

function voltarStep1SINAPI() {
  document.getElementById('sinapi-step1').style.display = 'block';
  document.getElementById('sinapi-step2').style.display = 'none';
  document.getElementById('sinapi-resultado').style.display = 'none';
  elementoAtualSINAPI = null;
  ultimoResultadoSINAPI = null;
}

// ══════════════════════════════════════════════
//  COLETAR DIMENSÕES
// ══════════════════════════════════════════════
function coletarDims() {
  if (!elementoAtualSINAPI) return null;
  const d = {};
  elementoAtualSINAPI.dims.forEach(dim => {
    const el = document.getElementById('d_sinapi_' + dim.id);
    d[dim.id] = parseFloat(el?.value) || 0;
  });
  elementoAtualSINAPI.extras?.forEach(ex => {
    if (ex.type === 'check') {
      const el = document.getElementById('ex_sinapi_' + ex.id);
      d[ex.id] = el ? el.checked : ex.default;
    } else if (ex.type === 'select') {
      const el = document.getElementById('ex_sinapi_' + ex.id);
      d[ex.id] = el ? el.value : ex.default;
    } else if (ex.type === 'input_cond') {
      const el = document.getElementById('ex_sinapi_' + ex.id);
      d[ex.id] = el ? parseFloat(el.value) || 0 : 0;
    }
  });
  return d;
}

// ══════════════════════════════════════════════
//  CALCULAR
// ══════════════════════════════════════════════
function calcularSINAPI() {
  if (!elementoAtualSINAPI) return;
  const d = coletarDims();
  const allOk = elementoAtualSINAPI.dims.every(dim => d[dim.id] > 0);
  if (!allOk) { 
    alert('Preencha todos os campos de dimensão com valores maiores que zero.'); 
    return; 
  }

  const result = elementoAtualSINAPI.calcFn(d);
  renderResultadoSINAPI(result, d);
}

// ══════════════════════════════════════════════
//  RENDER RESULTADO
// ══════════════════════════════════════════════
async function renderResultadoSINAPI(result, d) {
  const { qty, un, mats, nota } = result;

  // Consolidar materiais (agrupar por key+nome)
  const mapa = {};
  mats.forEach(m => {
    const k = (m.key||m.nome) + '||' + m.nome;
    if (!mapa[k]) {
      const base = PRECOS_SINAPI[m.key] || {};
      mapa[k] = {
        codigo_sinapi: m.codigo_sinapi || m.key,
        nome: m.nome,
        un: m.un || base.un || 'un',
        tipo: m.tipo || base.tipo || 'material',
        qty: 0,
        preco: m.precoOverride ?? base.preco ?? 0,
        key: m.key
      };
    }
    mapa[k].qty += m.qty;
  });

  let lista = Object.values(mapa).sort((a,b) => {
    const ordem = { material:0, mao:1, equip:2 };
    return (ordem[a.tipo]||0) - (ordem[b.tipo]||0);
  });
  
  // Buscar preços do banco de dados
  const codigosSinapi = [...new Set(
    lista.map(item => item.codigo_sinapi).filter(c => c && c.trim() !== '')
  )];
  
  if (codigosSinapi.length > 0) {
    const uf = document.getElementById('uf2')?.value || 'SP';
    const precosBanco = await buscarPrecosBanco(codigosSinapi, uf);
    
    console.log('📊 Preços do banco SINAPI:', precosBanco);
    
    // Atualizar preços com valores do banco
    lista = lista.map(item => {
      const precoBanco = precosBanco[item.codigo_sinapi];
      if (precoBanco && precoBanco.preco > 0) {
        console.log(`✓ Atualizando ${item.nome}: R$ ${item.preco} → R$ ${precoBanco.preco} (${precoBanco.unidade})`);
        return {
          ...item,
          preco: precoBanco.preco,
          un: precoBanco.unidade || item.un,
          fonte: 'banco_dados'
        };
      }
      console.log(`⚠ Mantendo preço hardcoded para ${item.nome}: R$ ${item.preco}`);
      return { ...item, fonte: 'hardcoded' };
    });
  }

  // Totais
  let totalMat = 0, totalMao = 0, totalEquip = 0;
  lista.forEach(item => {
    const sub = item.qty * item.preco;
    if (item.tipo === 'material') totalMat += sub;
    else if (item.tipo === 'mao') totalMao += sub;
    else totalEquip += sub;
  });
  const totalGeral = totalMat + totalMao + totalEquip;

  // Banner
  const dimsStr = elementoAtualSINAPI.dims.map(dim => `${dim.label}: ${fmt(d[dim.id],2)} ${dim.unit}`).join(' · ');
  document.getElementById('sinapi-res-el').textContent = elementoAtualSINAPI.nome;
  document.getElementById('sinapi-res-dims').textContent = nota || dimsStr;
  document.getElementById('sinapi-res-qty').textContent = fmt(qty, 3);
  document.getElementById('sinapi-res-un').textContent = un;
  document.getElementById('sinapi-res-total').textContent = fmtR(totalGeral);

  // Tabela
  const tipoLabel = { material:'material', mao:'mão de obra', equip:'equipamento' };
  const tipoClass = { material:'tipo-material', mao:'tipo-mao', equip:'tipo-equip' };

  let tbody = '';
  let curTipo = null;
  let itemIndex = 0;
  lista.forEach(item => {
    if (item.tipo !== curTipo) {
      curTipo = item.tipo;
      const titulos = { material:'🧱 Materiais', mao:'👷 Mão de Obra', equip:'⚙️ Equipamentos' };
      tbody += `<tr><td colspan="8" style="background:rgba(201,151,58,.1);padding:8px;font-size:10px;font-weight:700;letter-spacing:1px;color:#C9973A;text-transform:uppercase;">${titulos[curTipo]||curTipo}</td></tr>`;
    }
    const sub = item.qty * item.preco;
    const qtyFmt = item.qty >= 100 ? fmt(item.qty,1) : fmt(item.qty,3);
    const codigoEscaped = String(item.codigo_sinapi || '').replace(/'/g, "\\'");
    const unidadeEscaped = String(item.un || 'UN').replace(/'/g, "\\'");
    const nomeEscaped = String(item.nome || '').replace(/"/g, '&quot;').replace(/'/g, "\\'");
    tbody += `
      <tr data-item-index="${itemIndex}">
        <td style="padding:8px; text-align:center;">
          <input type="checkbox" class="sinapi-item-check" data-index="${itemIndex}" checked style="cursor:pointer; width:16px; height:16px;">
        </td>
        <td style="padding:8px; font-size:10px; color:#999;">${tipoLabel[item.tipo]}</td>
        <td style="padding:8px; font-size:11px;">
          <input type="text" 
                 class="sinapi-nome-input" 
                 data-index="${itemIndex}" 
                 data-codigo="${codigoEscaped}"
                 value="${nomeEscaped}" 
                 style="width:100%; padding:4px 6px; border:1px solid rgba(255,255,255,.1); border-radius:4px; background:rgba(255,255,255,.04); color:var(--text); font-size:11px;"
                 onchange="atualizarNomeSINAPI('${codigoEscaped}', this.value, ${itemIndex})">
        </td>
        <td style="padding:8px; text-align:right;">
          <input type="number" 
                 class="sinapi-qty-input" 
                 data-index="${itemIndex}" 
                 value="${item.qty.toFixed(3)}" 
                 step="0.001" 
                 min="0"
                 style="width:80px; padding:4px 6px; text-align:right; border:1px solid rgba(255,255,255,.1); border-radius:4px; background:rgba(255,255,255,.04); color:var(--text); font-size:11px;"
                 onchange="recalcularItemSINAPI(${itemIndex})">
        </td>
        <td style="padding:8px; text-align:center;">
          <input type="text" 
                 class="sinapi-un-input" 
                 data-index="${itemIndex}" 
                 data-codigo="${codigoEscaped}"
                 value="${unidadeEscaped}" 
                 maxlength="10"
                 style="width:60px; padding:4px 6px; text-align:center; border:1px solid rgba(255,255,255,.1); border-radius:4px; background:rgba(255,255,255,.04); color:var(--text); font-size:10px; text-transform:uppercase;"
                 onchange="atualizarUnidadeSINAPI('${codigoEscaped}', this.value, ${itemIndex})">
        </td>
        <td style="padding:8px; text-align:right;">
          <input type="number" 
                 class="sinapi-preco-input" 
                 data-index="${itemIndex}" 
                 data-codigo="${codigoEscaped}"
                 value="${item.preco.toFixed(2)}" 
                 step="0.01" 
                 min="0"
                 style="width:90px; padding:4px 6px; text-align:right; border:1px solid rgba(255,255,255,.1); border-radius:4px; background:rgba(255,255,255,.04); color:var(--text); font-size:11px;"
                 onchange="recalcularItemSINAPI(${itemIndex}); atualizarPrecoSINAPI('${codigoEscaped}', this.value, null)">
        </td>
        <td class="sinapi-subtotal-${itemIndex}" style="padding:8px; text-align:right; font-size:11px; font-weight:700;">${fmtR(sub)}</td>
        <td style="padding:4px; text-align:center; font-size:9px; color:#666;">
          ${item.fonte === 'banco_dados' ? '✓ SQL' : '⚠ Manual'}
        </td>
      </tr>`;
    itemIndex++;
  });

  document.getElementById('sinapi-mat-tbody').innerHTML = tbody;

  // Guardar resultado para adicionar ao orçamento
  ultimoResultadoSINAPI = { elemento: elementoAtualSINAPI.nome, lista, totalGeral };

  document.getElementById('sinapi-step2').style.display = 'none';
  document.getElementById('sinapi-resultado').style.display = 'block';
}

// ══════════════════════════════════════════════
//  ADICIONAR AO ORÇAMENTO
// ══════════════════════════════════════════════
function adicionarAoOrcamento() {
  if (!ultimoResultadoSINAPI || !ultimoResultadoSINAPI.lista) {
    alert('Nenhum cálculo disponível para adicionar.');
    return;
  }

  // Filtrar apenas itens selecionados
  const checkboxes = document.querySelectorAll('.sinapi-item-check');
  const itensSelecionados = [];
  
  checkboxes.forEach((cb, index) => {
    if (cb.checked && ultimoResultadoSINAPI.lista[index]) {
      itensSelecionados.push(ultimoResultadoSINAPI.lista[index]);
    }
  });

  if (itensSelecionados.length === 0) {
    alert('Selecione pelo menos um item para adicionar.');
    return;
  }

  const urlParams = new URLSearchParams(window.location.search);
  const orcamentoId = urlParams.get('id');
  
  if (!orcamentoId) {
    alert('ID do orçamento não encontrado.');
    return;
  }

  // Obter BDI configurado
  const bdiInput = document.getElementById('sinapi-bdi-input');
  const percentualBdi = parseFloat(bdiInput?.value || 25);

  const payload = {
    orcamento_id: parseInt(orcamentoId),
    elemento_nome: ultimoResultadoSINAPI.elemento,
    percentual_bdi: percentualBdi,
    itens: itensSelecionados
  };

  // Enviar para backend
  fetch('/?route=orcamentos/addFromSinapi', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert(`✓ ${data.count} itens adicionados com sucesso ao orçamento!`);
      window.location.reload();
    } else {
      alert('Erro ao adicionar itens: ' + (data.error || 'Erro desconhecido'));
    }
  })
  .catch(err => {
    console.error('Erro ao adicionar:', err);
    alert('Erro ao adicionar itens ao orçamento. Verifique o console.');
  });
}

// ══════════════════════════════════════════════
//  UTILS
// ══════════════════════════════════════════════
function fmt(v, dec=2) {
  return v.toLocaleString('pt-BR', { minimumFractionDigits:dec, maximumFractionDigits:dec });
}
function fmtR(v) {
  return 'R$ ' + fmt(v, 2);
}

// ══════════════════════════════════════════════
//  CSS DINÂMICO PARA CARDS
// ══════════════════════════════════════════════
(function() {
  const style = document.createElement('style');
  style.textContent = `
    .element-card:hover {
      background: rgba(201,151,58,.1) !important;
      border-color: rgba(201,151,58,.3) !important;
      transform: translateY(-2px);
    }
    .element-card.selected {
      background: rgba(201,151,58,.15) !important;
      border-color: rgba(201,151,58,.5) !important;
    }
  `;
  document.head.appendChild(style);
})();

// ══════════════════════════════════════════════
//  SELECIONAR/DESMARCAR TODOS
// ══════════════════════════════════════════════
function selecionarTodosSINAPI(checked) {
  document.querySelectorAll('.sinapi-item-check').forEach(cb => {
    cb.checked = checked;
  });
}

// ══════════════════════════════════════════════
//  BUSCAR PREÇOS DO BANCO DE DADOS
// ══════════════════════════════════════════════
async function buscarPrecosBanco(codigos, uf) {
  if (!codigos || codigos.length === 0) {
    return {};
  }
  
  try {
    const codigosStr = codigos.join(',');
    console.log(`🔍 Buscando preços SINAPI: ${codigosStr} (UF: ${uf})`);
    
    const response = await fetch(
      `/?api=sinapi-precos&codigos=${codigosStr}&uf=${uf}`
    );
    
    if (!response.ok) {
      console.warn('Erro ao buscar preços do banco:', response.status);
      return {};
    }
    
    const data = await response.json();
    console.log('📦 Resposta da API:', data);
    
    if (data.success && data.precos) {
      console.log(`✓ ${Object.keys(data.precos).length} preços encontrados no banco de dados`);
      return data.precos;
    }
    
    return {};
  } catch (error) {
    console.error('Erro ao buscar preços do banco:', error);
    return {};
  }
}


// ══════════════════════════════════════════════
//  RECALCULAR ITEM QUANDO QUANTIDADE OU PREÇO MUDAR
// ══════════════════════════════════════════════
function recalcularItemSINAPI(index) {
  if (!ultimoResultadoSINAPI || !ultimoResultadoSINAPI.lista[index]) {
    return;
  }
  
  const qtyInput = document.querySelector(`.sinapi-qty-input[data-index="${index}"]`);
  const precoInput = document.querySelector(`.sinapi-preco-input[data-index="${index}"]`);
  const unInput = document.querySelector(`.sinapi-un-input[data-index="${index}"]`);
  const subtotalCell = document.querySelector(`.sinapi-subtotal-${index}`);
  
  if (!qtyInput || !precoInput || !subtotalCell) {
    return;
  }
  
  const novaQty = parseFloat(qtyInput.value) || 0;
  const novoPreco = parseFloat(precoInput.value) || 0;
  const novaUnidade = unInput ? unInput.value.trim().toUpperCase() : ultimoResultadoSINAPI.lista[index].un;
  const novoSubtotal = novaQty * novoPreco;
  
  // Atualizar no objeto
  ultimoResultadoSINAPI.lista[index].qty = novaQty;
  ultimoResultadoSINAPI.lista[index].preco = novoPreco;
  ultimoResultadoSINAPI.lista[index].un = novaUnidade;
  
  // Atualizar display
  subtotalCell.textContent = fmtR(novoSubtotal);
  
  // Recalcular total geral
  let totalGeral = 0;
  ultimoResultadoSINAPI.lista.forEach(item => {
    totalGeral += item.qty * item.preco;
  });
  
  ultimoResultadoSINAPI.totalGeral = totalGeral;
  document.getElementById('sinapi-res-total').textContent = fmtR(totalGeral);
}

// ══════════════════════════════════════════════
//  ATUALIZAR NOME/DESCRIÇÃO NO BANCO DE DADOS
// ══════════════════════════════════════════════
let updateNomeTimeouts = {};

async function atualizarNomeSINAPI(codigo, novoNome, index) {
  if (!codigo || !novoNome) {
    console.log('⚠ Código ou nome inválido:', codigo, novoNome);
    return;
  }
  
  // Debounce
  if (updateNomeTimeouts[codigo]) {
    clearTimeout(updateNomeTimeouts[codigo]);
  }
  
  console.log(`⏱ Agendando atualização de nome para código ${codigo}...`);
  
  return new Promise((resolve) => {
    updateNomeTimeouts[codigo] = setTimeout(async () => {
      const uf = 'SP';
      
      const payload = {
        codigo: codigo,
        descricao: novoNome.trim(),
        uf: uf
      };
      
      console.log(`📤 Atualizando nome no banco:`, payload);
      
      try {
        const response = await fetch('/?api=sinapi-atualizar-descricao', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
          console.log(`✅ Nome atualizado!`);
          
          if (ultimoResultadoSINAPI && ultimoResultadoSINAPI.lista[index]) {
            ultimoResultadoSINAPI.lista[index].nome = novoNome.trim();
          }
          
          const input = document.querySelector(`.sinapi-nome-input[data-index="${index}"]`);
          if (input) {
            input.style.borderColor = '#4CAF50';
            input.style.background = 'rgba(76, 175, 80, 0.1)';
            setTimeout(() => {
              input.style.borderColor = '';
              input.style.background = '';
            }, 2000);
          }
        }
        
        resolve(data);
      } catch (error) {
        console.error('❌ Erro:', error);
        resolve({ success: false, error: error.message });
      }
    }, 1500);
  });
}

// ══════════════════════════════════════════════
//  ATUALIZAR PREÇO NO BANCO DE DADOS
// ══════════════════════════════════════════════
let updateTimeouts = {};

async function atualizarPrecoSINAPI(codigo, novoPreco, unidade) {
  if (!codigo || !novoPreco) {
    console.log('⚠ Código ou preço inválido:', codigo, novoPreco);
    return;
  }
  
  // Debounce: cancelar atualização anterior para este código
  if (updateTimeouts[codigo]) {
    console.log(`⏱ Cancelando atualização anterior para código ${codigo}`);
    clearTimeout(updateTimeouts[codigo]);
  }
  
  console.log(`⏱ Agendando atualização para código ${codigo} em 1.5 segundos...`);
  
  // Aguardar 1.5 segundos antes de enviar
  return new Promise((resolve) => {
    updateTimeouts[codigo] = setTimeout(async () => {
      const uf = 'SP';
      
      const payload = {
        codigo: codigo,
        preco: parseFloat(novoPreco),
        uf: uf
      };
      
      if (unidade) {
        payload.unidade = unidade.trim().toUpperCase();
      }
      
      console.log(`📤 ENVIANDO PARA O BANCO:`, payload);
      console.log(`   URL: /?api=sinapi-atualizar-preco`);
      console.log(`   Método: POST`);
      console.log(`   Body:`, JSON.stringify(payload));
      
      try {
        const response = await fetch('/?api=sinapi-atualizar-preco', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        });
        
        console.log('📥 Resposta HTTP:', response.status, response.statusText);
        
        const responseText = await response.text();
        console.log('📄 Resposta completa (primeiros 500 chars):', responseText.substring(0, 500));
        
        if (!response.ok) {
          console.error(`❌ HTTP ${response.status}:`, responseText);
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        let data;
        try {
          data = JSON.parse(responseText);
        } catch (e) {
          console.error('❌ Erro ao parsear JSON:', e);
          console.error('Resposta recebida:', responseText);
          throw new Error('Resposta inválida do servidor');
        }
        
        console.log('📊 JSON parseado:', data);
        
        if (data.success) {
          console.log(`✅ ✅ ✅ SALVO NO BANCO COM SUCESSO! ✅ ✅ ✅`);
          console.log(`   Código: ${codigo}`);
          console.log(`   Preço: R$ ${novoPreco}`);
          console.log(`   Unidade: ${unidade || 'não alterada'}`);
          console.log(`   Preço anterior: R$ ${data.preco_anterior || 'N/A'}`);
          
          // Mostrar feedback visual
          const input = document.querySelector(`.sinapi-preco-input[data-codigo="${codigo}"]`);
          if (input) {
            input.style.borderColor = '#4CAF50';
            input.style.background = 'rgba(76, 175, 80, 0.1)';
            input.title = `Salvo: R$ ${novoPreco}`;
            setTimeout(() => {
              input.style.borderColor = '';
              input.style.background = '';
              input.title = '';
            }, 3000);
          }
        } else {
          console.error('❌ Falha ao salvar:', data.error);
          console.warn('Detalhes completos:', data);
        }
        
        resolve(data);
      } catch (error) {
        console.error('❌ ❌ ❌ ERRO AO SALVAR NO BANCO:', error);
        console.error('Tipo do erro:', error.constructor.name);
        console.error('Mensagem:', error.message);
        console.error('Stack:', error.stack);
        resolve({ success: false, error: error.message });
      }
    }, 1500); // 1.5 segundos
  });
}

// ══════════════════════════════════════════════
//  ATUALIZAR UNIDADE NO BANCO DE DADOS
// ══════════════════════════════════════════════
async function atualizarUnidadeSINAPI(codigo, novaUnidade, index) {
  console.log('═══════════════════════════════════════════════════════');
  console.log('🔧 INICIANDO atualizarUnidadeSINAPI');
  console.log('   Código:', codigo);
  console.log('   Nova Unidade:', novaUnidade);
  console.log('   Index:', index);
  console.log('═══════════════════════════════════════════════════════');
  
  if (!codigo || !novaUnidade) {
    console.log('⚠ Código ou unidade inválida:', codigo, novaUnidade);
    return;
  }
  
  const unidadeFormatada = novaUnidade.trim().toUpperCase();
  console.log(`🔄 Tentando alterar unidade do código ${codigo} para ${unidadeFormatada}`);
  
  // Buscar o item atual no banco
  try {
    const uf = 'SP';
    console.log(`📡 Fazendo requisição: /?api=sinapi-precos&codigo=${codigo}&uf=${uf}`);
    const response = await fetch(`/?api=sinapi-precos&codigo=${codigo}&uf=${uf}`);
    
    console.log(`📡 Status da resposta: ${response.status} ${response.statusText}`);
    
    if (!response.ok) {
      console.error('❌ Erro na resposta:', response.status);
      return;
    }
    
    const data = await response.json();
    console.log('📦 Item atual no banco:', data);
    
    if (data.success) {
      const unidadeBanco = data.unidade;
      const descricaoAtual = data.descricao;
      
      console.log(`   Unidade no banco: ${unidadeBanco}`);
      console.log(`   Unidade desejada: ${unidadeFormatada}`);
      
      if (unidadeBanco === unidadeFormatada) {
        console.log(`✓ Unidade ${unidadeFormatada} já é a correta para este código`);
        return;
      }
      
      // Unidade diferente - buscar código alternativo com a mesma descrição
      console.log(`🔍 Buscando código alternativo com unidade ${unidadeFormatada}...`);
      console.log(`   Descrição base: ${descricaoAtual}`);
      
      // Extrair apenas a PRIMEIRA palavra significativa (mais genérico)
      const palavras = descricaoAtual.toUpperCase().split(' ');
      const palavraChavePrincipal = palavras.find(p => 
        p.length > 4 && 
        !['CAPACIDADE', 'NOMINAL', 'MOTOR', 'POTENCIA', 'ELETRICO', 'TRIFASICO'].includes(p)
      ) || palavras[0];
      
      console.log(`   Palavra-chave principal: ${palavraChavePrincipal}`);
      
      // Buscar no banco por descrição similar com limite maior
      const searchResponse = await fetch(`/?api=sinapi-precos&listar=1&termo=${encodeURIComponent(palavraChavePrincipal)}&uf=${uf}&limite=100`);
      const searchData = await searchResponse.json();
      
      console.log(`📋 Encontrados ${searchData.total} resultados similares para "${palavraChavePrincipal}"`);
      
      if (searchData.success && searchData.insumos && searchData.insumos.length > 0) {
        console.log(`📋 Primeiros 15 resultados encontrados:`);
        searchData.insumos.slice(0, 15).forEach(item => {
          console.log(`   - ${item.codigo}: ${item.descricao.substring(0, 60)}... (${item.unidade}) - R$ ${item.preco_unit}`);
        });
        
        // Se buscar por H, priorizar CHP (Custo Horário Produtivo - aluguel)
        let itemComUnidade;
        
        if (unidadeFormatada === 'H') {
          console.log(`🎯 Buscando custo horário de aluguel (CHP ou H)...`);
          
          // Estratégia 1: Buscar CHP DIURNO (custo de aluguel completo)
          itemComUnidade = searchData.insumos.find(item => 
            item.unidade === 'CHP' && 
            item.descricao.toUpperCase().includes('CHP DIURNO')
          );
          
          if (itemComUnidade) {
            console.log(`✓ Encontrado CHP DIURNO: ${itemComUnidade.codigo}`);
          }
          
          // Estratégia 2: Se não encontrar CHP DIURNO, buscar qualquer CHP
          if (!itemComUnidade) {
            console.log(`⚠ CHP DIURNO não encontrado, buscando qualquer CHP...`);
            itemComUnidade = searchData.insumos.find(item => item.unidade === 'CHP');
            
            if (itemComUnidade) {
              console.log(`✓ Encontrado CHP: ${itemComUnidade.codigo}`);
            }
          }
          
          // Estratégia 3: Se não encontrar CHP, buscar H excluindo componentes
          if (!itemComUnidade) {
            console.log(`⚠ CHP não encontrado, buscando H (excluindo componentes)...`);
            const componentesExcluir = [
              'DEPRECIAÇÃO', 'DEPRECIAÇAO', 'DEPRECIACAO',
              'JUROS', 
              'MANUTENÇÃO', 'MANUTENCAO', 'MANUTENÇÃO',
              'MATERIAIS NA OPERAÇÃO', 'MATERIAIS NA OPERACAO',
              'MATERIAIS'
            ];
            
            itemComUnidade = searchData.insumos.find(item => 
              item.unidade === 'H' &&
              !componentesExcluir.some(comp => item.descricao.toUpperCase().includes(comp))
            );
            
            if (itemComUnidade) {
              console.log(`✓ Encontrado H: ${itemComUnidade.codigo}`);
            }
          }
        } else {
          // Para outras unidades, busca normal
          console.log(`🔍 Buscando unidade ${unidadeFormatada}...`);
          itemComUnidade = searchData.insumos.find(item => 
            item.unidade === unidadeFormatada
          );
        }
        
        if (itemComUnidade) {
          console.log(`✅ Código alternativo encontrado!`);
          console.log(`   Código novo: ${itemComUnidade.codigo}`);
          console.log(`   Descrição: ${itemComUnidade.descricao}`);
          console.log(`   Unidade: ${itemComUnidade.unidade}`);
          console.log(`   Preço: R$ ${itemComUnidade.preco_unit || itemComUnidade.preco}`);
          
          const novoPreco = parseFloat(itemComUnidade.preco_unit || itemComUnidade.preco);
          const novaUnidadeReal = itemComUnidade.unidade; // Pode ser CHP se buscou por H
          
          // Atualizar todos os campos
          const precoInput = document.querySelector(`.sinapi-preco-input[data-index="${index}"]`);
          const unInput = document.querySelector(`.sinapi-un-input[data-index="${index}"]`);
          const qtyInput = document.querySelector(`.sinapi-qty-input[data-index="${index}"]`);
          const nomeCell = document.querySelector(`tr[data-item-index="${index}"] td:nth-child(3)`);
          
          if (precoInput) {
            precoInput.value = novoPreco.toFixed(2);
            precoInput.setAttribute('data-codigo', itemComUnidade.codigo);
            // Atualizar o onchange para usar o novo código
            precoInput.setAttribute('onchange', `recalcularItemSINAPI(${index}); atualizarPrecoSINAPI('${itemComUnidade.codigo}', this.value, null)`);
          }
          
          if (unInput) {
            // Mostrar H para o usuário, mas internamente é CHP
            unInput.value = unidadeFormatada === 'H' && novaUnidadeReal === 'CHP' ? 'H' : novaUnidadeReal;
            unInput.setAttribute('data-codigo', itemComUnidade.codigo);
            unInput.setAttribute('data-unidade-real', novaUnidadeReal); // Guardar unidade real
            // Atualizar o onchange para usar o novo código
            unInput.setAttribute('onchange', `atualizarUnidadeSINAPI('${itemComUnidade.codigo}', this.value, ${index})`);
          }
          
          if (nomeCell) {
            const nomeEscaped = String(itemComUnidade.descricao).replace(/"/g, '&quot;').replace(/'/g, "\\'");
            const codigoEscaped = String(itemComUnidade.codigo).replace(/'/g, "\\'");
            nomeCell.innerHTML = `<input type="text" 
                 class="sinapi-nome-input" 
                 data-index="${index}" 
                 data-codigo="${codigoEscaped}"
                 value="${nomeEscaped}" 
                 style="width:100%; padding:4px 6px; border:1px solid rgba(255,255,255,.1); border-radius:4px; background:rgba(255,255,255,.04); color:var(--text); font-size:11px;"
                 onchange="atualizarNomeSINAPI('${codigoEscaped}', this.value, ${index})">`;
          }
          
          // Atualizar objeto local
          if (ultimoResultadoSINAPI && ultimoResultadoSINAPI.lista[index]) {
            ultimoResultadoSINAPI.lista[index].codigo_sinapi = itemComUnidade.codigo;
            ultimoResultadoSINAPI.lista[index].preco = novoPreco;
            ultimoResultadoSINAPI.lista[index].un = novaUnidadeReal; // Guardar unidade real (CHP)
            ultimoResultadoSINAPI.lista[index].nome = itemComUnidade.descricao;
            ultimoResultadoSINAPI.lista[index].fonte = 'banco_dados';
          }
          
          // Recalcular subtotal
          recalcularItemSINAPI(index);
          
          // Feedback visual de sucesso
          if (unInput) {
            unInput.style.borderColor = '#4CAF50';
            unInput.style.background = 'rgba(76, 175, 80, 0.1)';
            setTimeout(() => {
              unInput.style.borderColor = '';
              unInput.style.background = '';
            }, 2000);
          }
          
          console.log(`✅ Item atualizado com sucesso para código ${itemComUnidade.codigo}!`);
          
          return;
        }
      }
      
      // Não encontrou código alternativo
      console.warn(`⚠ Não foi encontrado código SINAPI com unidade ${unidadeFormatada} para este item`);
      console.warn(`💡 Mantendo código ${codigo} com unidade ${unidadeBanco}`);
      
      // Reverter para a unidade original
      const unInput = document.querySelector(`.sinapi-un-input[data-index="${index}"]`);
      if (unInput) {
        unInput.value = unidadeBanco;
        
        // Feedback visual de aviso
        unInput.style.borderColor = '#ff9800';
        unInput.style.background = 'rgba(255, 152, 0, 0.1)';
        setTimeout(() => {
          unInput.style.borderColor = '';
          unInput.style.background = '';
        }, 2000);
      }
      
      // Atualizar objeto local
      if (ultimoResultadoSINAPI && ultimoResultadoSINAPI.lista[index]) {
        ultimoResultadoSINAPI.lista[index].un = unidadeBanco;
      }
      
      alert(`⚠ Não foi encontrado código SINAPI com unidade ${unidadeFormatada} para:\n"${descricaoAtual}"\n\nMantenha a unidade ${unidadeBanco} ou busque manualmente o código correto.`);
    }
  } catch (error) {
    console.error('❌ Erro ao buscar código alternativo:', error);
  }
}
