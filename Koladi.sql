--
-- PostgreSQL database dump
--

\restrict fJDXhXHGsiotnyWxaP4rR4ZBzRilXUSAwWMce3dpkYz8bxlWXYa6IeLJgKH7By3

-- Dumped from database version 15.15 (Debian 15.15-1.pgdg13+1)
-- Dumped by pg_dump version 15.15 (Debian 15.15-1.pgdg13+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- Name: conversation_scope; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.conversation_scope AS ENUM (
    'workspace',
    'company'
);


ALTER TYPE public.conversation_scope OWNER TO postgres;

--
-- Name: payment_method_enum; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.payment_method_enum AS ENUM (
    'midtrans',
    'manual',
    'xendit'
);


ALTER TYPE public.payment_method_enum OWNER TO postgres;

--
-- Name: update_updated_at_column(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_updated_at_column() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
   NEW.updated_at = NOW();
   RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_updated_at_column() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: addons; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.addons (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    addon_name character varying(100) NOT NULL,
    price_per_user numeric(12,2) NOT NULL,
    description text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.addons OWNER TO postgres;

--
-- Name: announcement_recipients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.announcement_recipients (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    announcement_id uuid,
    user_id uuid
);


ALTER TABLE public.announcement_recipients OWNER TO postgres;

--
-- Name: announcements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.announcements (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    title character varying(255) NOT NULL,
    description text,
    due_date date,
    is_private boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    auto_due date,
    company_id uuid
);


ALTER TABLE public.announcements OWNER TO postgres;

--
-- Name: attachments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.attachments (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    attachable_type character varying(100),
    attachable_id uuid,
    file_url text NOT NULL,
    uploaded_by uuid,
    uploaded_at timestamp without time zone DEFAULT now(),
    file_name character varying(255),
    file_size bigint,
    file_type character varying(100),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.attachments OWNER TO postgres;

--
-- Name: board_columns; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.board_columns (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    name character varying(255) NOT NULL,
    "position" integer,
    created_by uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone
);


ALTER TABLE public.board_columns OWNER TO postgres;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: calendar_events; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.calendar_events (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    title character varying(255) NOT NULL,
    description text,
    start_datetime timestamp without time zone,
    end_datetime timestamp without time zone,
    recurrence character varying(100),
    is_private boolean DEFAULT false,
    is_online_meeting boolean DEFAULT false,
    meeting_link text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    company_id uuid,
    location character varying(255)
);


ALTER TABLE public.calendar_events OWNER TO postgres;

--
-- Name: calendar_participants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.calendar_participants (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    event_id uuid,
    user_id uuid,
    status character varying(50),
    attendance boolean DEFAULT false
);


ALTER TABLE public.calendar_participants OWNER TO postgres;

--
-- Name: checklists; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.checklists (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    task_id uuid,
    title character varying(255) NOT NULL,
    is_done boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    "position" integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.checklists OWNER TO postgres;

--
-- Name: colors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.colors (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    rgb character varying(20) NOT NULL
);


ALTER TABLE public.colors OWNER TO postgres;

--
-- Name: comments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.comments (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    parent_comment_id uuid,
    commentable_type character varying(100),
    commentable_id uuid,
    user_id uuid,
    content text NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone
);


ALTER TABLE public.comments OWNER TO postgres;

--
-- Name: companies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.companies (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    address text,
    phone character varying(50),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    trial_start timestamp(0) without time zone,
    trial_end timestamp(0) without time zone,
    status character varying(255) DEFAULT 'trial'::character varying NOT NULL,
    CONSTRAINT companies_status_check CHECK (((status)::text = ANY (ARRAY[('trial'::character varying)::text, ('active'::character varying)::text, ('expired'::character varying)::text, ('canceled'::character varying)::text])))
);


ALTER TABLE public.companies OWNER TO postgres;

--
-- Name: conversation_participants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.conversation_participants (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    conversation_id uuid,
    user_id uuid,
    joined_at timestamp without time zone DEFAULT now(),
    is_admin boolean DEFAULT false,
    last_read_at timestamp without time zone
);


ALTER TABLE public.conversation_participants OWNER TO postgres;

--
-- Name: conversations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.conversations (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_at timestamp without time zone DEFAULT now(),
    type character varying(50) DEFAULT 'group'::character varying,
    name character varying(255),
    created_by uuid,
    updated_at timestamp without time zone,
    last_message_id uuid,
    scope public.conversation_scope DEFAULT 'workspace'::public.conversation_scope NOT NULL,
    company_id uuid
);


ALTER TABLE public.conversations OWNER TO postgres;

--
-- Name: document_recipients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.document_recipients (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    document_id uuid NOT NULL,
    user_id uuid NOT NULL,
    status boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.document_recipients OWNER TO postgres;

--
-- Name: feedbacks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.feedbacks (
    id bigint NOT NULL,
    name character varying(255),
    email character varying(255),
    message text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.feedbacks OWNER TO postgres;

--
-- Name: feedbacks_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.feedbacks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.feedbacks_id_seq OWNER TO postgres;

--
-- Name: feedbacks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.feedbacks_id_seq OWNED BY public.feedbacks.id;


--
-- Name: files; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.files (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    folder_id uuid,
    workspace_id uuid,
    file_url text NOT NULL,
    is_private boolean DEFAULT false,
    uploaded_by uuid,
    uploaded_at timestamp without time zone DEFAULT now(),
    file_name character varying(255),
    file_path character varying(255),
    file_size integer,
    file_type character varying(255),
    company_id uuid,
    preview_image_url text
);


ALTER TABLE public.files OWNER TO postgres;

--
-- Name: folders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.folders (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    name character varying(255) NOT NULL,
    is_private boolean DEFAULT false,
    created_by uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    parent_id uuid,
    company_id uuid
);


ALTER TABLE public.folders OWNER TO postgres;

--
-- Name: insight_recipients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.insight_recipients (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    insight_id uuid,
    user_id uuid
);


ALTER TABLE public.insight_recipients OWNER TO postgres;

--
-- Name: insights; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.insights (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    description text,
    delivery_days character varying(50),
    delivery_time time without time zone,
    is_private boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.insights OWNER TO postgres;

--
-- Name: invitations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.invitations (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    email_target character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    status character varying(50) DEFAULT 'pending'::character varying,
    invited_by uuid,
    company_id uuid,
    created_at timestamp without time zone DEFAULT now(),
    expired_at timestamp without time zone,
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.invitations OWNER TO postgres;

--
-- Name: labels; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.labels (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(255) NOT NULL,
    color_id uuid,
    created_at timestamp(0) without time zone DEFAULT '2025-11-04 17:33:38'::timestamp without time zone NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT '2025-11-04 17:33:38'::timestamp without time zone NOT NULL,
    workspace_id uuid NOT NULL
);


ALTER TABLE public.labels OWNER TO postgres;

--
-- Name: leave_requests; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.leave_requests (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid,
    workspace_id uuid,
    leave_type character varying(100),
    start_date date,
    end_date date,
    reason text,
    status character varying(50) DEFAULT 'pending'::character varying,
    approved_by uuid,
    attachment_url text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.leave_requests OWNER TO postgres;

--
-- Name: messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.messages (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    conversation_id uuid,
    sender_id uuid,
    content text,
    message_type character varying(50),
    reply_to_message_id uuid,
    is_edited boolean DEFAULT false,
    edited_at timestamp without time zone,
    deleted_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    is_read boolean DEFAULT false,
    read_at timestamp without time zone,
    updated_at timestamp without time zone
);


ALTER TABLE public.messages OWNER TO postgres;

--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: mindmap_nodes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mindmap_nodes (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    mindmap_id uuid NOT NULL,
    parent_id uuid,
    title character varying(255) NOT NULL,
    description text,
    type character varying(50) DEFAULT 'default'::character varying,
    x_position numeric(10,2) DEFAULT 0,
    y_position numeric(10,2) DEFAULT 0,
    connection_side character varying(20) DEFAULT 'auto'::character varying,
    sort_order integer DEFAULT 0,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.mindmap_nodes OWNER TO postgres;

--
-- Name: TABLE mindmap_nodes; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.mindmap_nodes IS 'Tabel untuk menyimpan node-node dalam mind map';


--
-- Name: mindmaps; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mindmaps (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid NOT NULL,
    title character varying(255) DEFAULT 'Mind Map Utama'::character varying NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.mindmaps OWNER TO postgres;

--
-- Name: TABLE mindmaps; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.mindmaps IS 'Tabel untuk menyimpan mind map dalam workspace';


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.notifications (
    id uuid NOT NULL,
    user_id uuid NOT NULL,
    company_id uuid NOT NULL,
    workspace_id uuid,
    type character varying(255) NOT NULL,
    title character varying(255) NOT NULL,
    message text NOT NULL,
    context character varying(255),
    notifiable_type character varying(255) NOT NULL,
    notifiable_id uuid NOT NULL,
    actor_id uuid,
    is_read boolean DEFAULT false NOT NULL,
    read_at timestamp(0) without time zone,
    action_url character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT notifications_type_check CHECK (((type)::text = ANY (ARRAY[('chat'::character varying)::text, ('task'::character varying)::text, ('announcement'::character varying)::text, ('schedule'::character varying)::text])))
);


ALTER TABLE public.notifications OWNER TO postgres;

--
-- Name: otp_verifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.otp_verifications (
    id bigint NOT NULL,
    email character varying(255) NOT NULL,
    otp character varying(6) NOT NULL,
    type character varying(20) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    is_used boolean DEFAULT false NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    CONSTRAINT otp_verifications_type_check CHECK (((type)::text = ANY (ARRAY[('register'::character varying)::text, ('reset_password'::character varying)::text])))
);


ALTER TABLE public.otp_verifications OWNER TO postgres;

--
-- Name: otp_verifications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.otp_verifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.otp_verifications_id_seq OWNER TO postgres;

--
-- Name: otp_verifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.otp_verifications_id_seq OWNED BY public.otp_verifications.id;


--
-- Name: plans; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.plans (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    plan_name character varying(100) NOT NULL,
    price_monthly numeric(12,2) NOT NULL,
    base_user_limit integer NOT NULL,
    description text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.plans OWNER TO postgres;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    name character varying(100) NOT NULL
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id uuid,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: subscription_invoices; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.subscription_invoices (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    subscription_id uuid NOT NULL,
    external_id character varying(255),
    payment_url character varying(255),
    amount numeric(12,2) NOT NULL,
    billing_month character varying(20) NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    paid_at timestamp(0) without time zone,
    payment_details text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    payment_method public.payment_method_enum DEFAULT 'midtrans'::public.payment_method_enum NOT NULL,
    proof_of_payment character varying(500),
    admin_notes text,
    verified_at timestamp without time zone,
    verified_by uuid,
    payer_name character varying(255),
    payer_bank character varying(100),
    payer_account_number character varying(50),
    CONSTRAINT subscription_invoices_status_check CHECK (((status)::text = ANY (ARRAY[('pending'::character varying)::text, ('paid'::character varying)::text, ('failed'::character varying)::text, ('expired'::character varying)::text])))
);


ALTER TABLE public.subscription_invoices OWNER TO postgres;

--
-- Name: subscriptions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.subscriptions (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    company_id uuid NOT NULL,
    plan_id uuid,
    addons_user_count integer DEFAULT 0 NOT NULL,
    total_user_limit integer DEFAULT 0 NOT NULL,
    start_date timestamp(0) without time zone,
    end_date timestamp(0) without time zone,
    status character varying(255) DEFAULT 'trial'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT subscriptions_status_check CHECK (((status)::text = ANY (ARRAY[('trial'::character varying)::text, ('active'::character varying)::text, ('expired'::character varying)::text, ('canceled'::character varying)::text, ('pending'::character varying)::text])))
);


ALTER TABLE public.subscriptions OWNER TO postgres;

--
-- Name: task_assignments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.task_assignments (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    task_id uuid,
    user_id uuid,
    assigned_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.task_assignments OWNER TO postgres;

--
-- Name: task_labels; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.task_labels (
    task_id uuid NOT NULL,
    label_id uuid NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.task_labels OWNER TO postgres;

--
-- Name: tasks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tasks (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    workspace_id uuid,
    created_by uuid,
    title character varying(255) NOT NULL,
    description text,
    status character varying(100),
    board_column_id uuid,
    priority character varying(50),
    is_secret boolean DEFAULT false,
    start_datetime timestamp without time zone,
    due_datetime timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    phase character varying(100),
    completed_at timestamp with time zone
);


ALTER TABLE public.tasks OWNER TO postgres;

--
-- Name: user_companies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_companies (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid,
    company_id uuid,
    roles_id uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    status_active boolean DEFAULT true NOT NULL
);


ALTER TABLE public.user_companies OWNER TO postgres;

--
-- Name: user_workspaces; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_workspaces (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    user_id uuid,
    workspace_id uuid,
    roles_id uuid,
    join_date timestamp without time zone DEFAULT now(),
    status_active boolean DEFAULT true,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.user_workspaces OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    full_name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    password text NOT NULL,
    google_id character varying(255),
    status_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    avatar character varying(500),
    email_verified_at timestamp without time zone,
    onboarding_step character varying(255),
    has_seen_onboarding boolean DEFAULT false,
    onboarding_type character varying(255),
    system_role_id uuid
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: workspace_performance_snapshots; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.workspace_performance_snapshots (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    workspace_id uuid NOT NULL,
    period_start date NOT NULL,
    period_end date NOT NULL,
    period_type character varying(10) DEFAULT 'week'::character varying NOT NULL,
    metrics jsonb NOT NULL,
    performance_score integer DEFAULT 0 NOT NULL,
    quality_score integer DEFAULT 0 NOT NULL,
    risk_score integer DEFAULT 0 NOT NULL,
    suggestions jsonb NOT NULL,
    created_at timestamp(0) without time zone DEFAULT now(),
    updated_at timestamp(0) without time zone DEFAULT now(),
    version character varying(10) DEFAULT '1.0'::character varying NOT NULL
);


ALTER TABLE public.workspace_performance_snapshots OWNER TO postgres;

--
-- Name: workspaces; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.workspaces (
    id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
    company_id uuid,
    type character varying(100),
    name character varying(255) NOT NULL,
    created_by uuid,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    description text
);


ALTER TABLE public.workspaces OWNER TO postgres;

--
-- Name: feedbacks id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.feedbacks ALTER COLUMN id SET DEFAULT nextval('public.feedbacks_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: otp_verifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications ALTER COLUMN id SET DEFAULT nextval('public.otp_verifications_id_seq'::regclass);


--
-- Data for Name: addons; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.addons (id, addon_name, price_per_user, description, is_active, created_at, updated_at) FROM stdin;
6e06fbfd-26f0-4fc4-898d-5e22c3e5833d	Tambahan User	4000.00	Tambah 1 user ke paket yang kamu pilih	t	2025-12-28 13:16:13	2025-12-28 13:16:13
\.


--
-- Data for Name: announcement_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.announcement_recipients (id, announcement_id, user_id) FROM stdin;
\.


--
-- Data for Name: announcements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.announcements (id, workspace_id, created_by, title, description, due_date, is_private, created_at, updated_at, auto_due, company_id) FROM stdin;
052855d4-8f3c-467b-abaf-5894e61a71f7	325797d8-e3ad-4e66-a280-a8098d195bc8	019ce093-8522-725c-8f9c-9b3928ec6ad3	lkm	<p>kjjk</p>	2026-03-16	f	2026-03-15 22:58:56	2026-03-15 22:58:56	2026-03-16	\N
a63b041a-ccce-4737-8089-e8ba8adffe79	\N	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	pengumuman gajian	<p>harap di tunggu informasinya</p>	2026-04-03	f	2026-04-02 16:05:45	2026-04-02 16:05:45	2026-04-03	31c7b915-01ea-40ed-80be-723ffe01c10d
228e6dcf-4b79-4a80-b95d-fd4acd30eb5b	173a617f-c955-42f6-9042-9815a0553ae6	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	project terbaru	<p>ada project baru dari klien</p>	2026-04-05	f	2026-04-02 16:06:55	2026-04-02 16:06:55	2026-04-05	\N
d6ac3bdc-7dec-4caa-882b-8dd1352774e9	\N	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	prubahan penempatan kerja	<p>di ubah menjadi 3</p>	2026-04-03	f	2026-04-02 16:09:08	2026-04-02 16:09:35	\N	31c7b915-01ea-40ed-80be-723ffe01c10d
f3426385-a986-4497-b1d9-f0c2732a0dbe	173a617f-c955-42f6-9042-9815a0553ae6	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	ada rapat penting harap berkumpul	<p>di ruang a lantai 3</p>	2026-04-05	f	2026-04-02 16:11:29	2026-04-02 16:11:29	2026-04-05	\N
6ce8e396-7497-498d-bb0a-a607d337fffd	\N	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	pengumuman anggota terbaru yang baru masuk	<p>lihat dan hitung anggota baru</p>	2026-04-05	f	2026-04-02 16:50:06	2026-04-02 16:50:32	\N	31c7b915-01ea-40ed-80be-723ffe01c10d
c5c3e3ec-d00a-42c9-b57e-a69ea0da31e8	173a617f-c955-42f6-9042-9815a0553ae6	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	jangan ada yang pulang duluan hari rabu	<p>jangan ada yang melanggar</p>	2026-04-03	f	2026-04-02 16:52:28	2026-04-02 16:52:28	2026-04-03	\N
\.


--
-- Data for Name: attachments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.attachments (id, attachable_type, attachable_id, file_url, uploaded_by, uploaded_at, file_name, file_size, file_type, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: board_columns; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.board_columns (id, workspace_id, name, "position", created_by, created_at, updated_at, deleted_at) FROM stdin;
960b37f9-4854-4e84-9de6-2802b6b13113	f7b296c0-c0b4-496b-a55e-b99e3692e1cf	To Do List	1	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	2025-12-28 13:16:52	2025-12-28 13:16:52	\N
9ad43e5c-f152-471d-afca-01b7ac94a3a3	f7b296c0-c0b4-496b-a55e-b99e3692e1cf	Dikerjakan	2	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	2025-12-28 13:16:52	2025-12-28 13:16:52	\N
d28a0b6f-f2e0-4f21-af96-76a5ad667882	f7b296c0-c0b4-496b-a55e-b99e3692e1cf	Selesai	3	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	2025-12-28 13:16:52	2025-12-28 13:16:52	\N
40b43736-3734-4835-a82f-de56f223fd73	f7b296c0-c0b4-496b-a55e-b99e3692e1cf	Batal	4	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	2025-12-28 13:16:52	2025-12-28 13:16:52	\N
fa8466a6-d9f7-4141-b2dd-2e6df2dc13cb	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	To Do List	1	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 10:30:24	2026-03-08 10:30:24	\N
d4f46630-c9e8-494b-bf45-8b49bd1a1364	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	Dikerjakan	2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 10:30:24	2026-03-08 10:30:24	\N
a872f537-6c00-4a4a-9be6-e723bfcae5d1	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	Selesai	3	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 10:30:24	2026-03-08 10:30:24	\N
43a2e96e-996e-408a-9279-0d4445b23711	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	Batal	4	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 10:30:24	2026-03-08 10:30:24	\N
49e53eaa-472c-496b-8b7d-727013c8f4ed	826f878b-ea65-43cd-923d-531b3ddf4599	To Do List	1	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 17:00:52	2026-03-08 17:00:52	\N
fbe11f73-0b8b-4605-9d24-c59d4f20942d	826f878b-ea65-43cd-923d-531b3ddf4599	Dikerjakan	2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 17:00:52	2026-03-08 17:00:52	\N
8c13d9e9-7867-4d96-abcd-9c5259ce3b67	826f878b-ea65-43cd-923d-531b3ddf4599	Selesai	3	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 17:00:52	2026-03-08 17:00:52	\N
dbf053a9-e95d-46b3-b8c6-d83838ee43ea	826f878b-ea65-43cd-923d-531b3ddf4599	Batal	4	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 17:00:52	2026-03-08 17:00:52	\N
33333903-5f5f-480e-8c29-1e3262496c79	325797d8-e3ad-4e66-a280-a8098d195bc8	To Do List	1	019ce093-8522-725c-8f9c-9b3928ec6ad3	2026-03-15 22:57:44	2026-03-15 22:57:44	\N
873269f3-9043-4fad-a924-eaea0bc6be8f	325797d8-e3ad-4e66-a280-a8098d195bc8	Dikerjakan	2	019ce093-8522-725c-8f9c-9b3928ec6ad3	2026-03-15 22:57:44	2026-03-15 22:57:44	\N
c70459d3-c21b-4e1d-bfd8-e5fc060d9840	325797d8-e3ad-4e66-a280-a8098d195bc8	Selesai	3	019ce093-8522-725c-8f9c-9b3928ec6ad3	2026-03-15 22:57:44	2026-03-15 22:57:44	\N
9b98083b-dece-4ff3-a931-40a924a48c21	325797d8-e3ad-4e66-a280-a8098d195bc8	Batal	4	019ce093-8522-725c-8f9c-9b3928ec6ad3	2026-03-15 22:57:44	2026-03-15 22:57:44	\N
8cf170fb-2a0a-4ea3-8931-af4d71684b8a	3b61e2cf-dd4b-4732-ae89-8042450187b2	To Do List	1	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 14:58:18	2026-03-29 14:58:18	\N
5910b39d-5ad7-4be9-8b61-26c907c8e1b1	3b61e2cf-dd4b-4732-ae89-8042450187b2	Dikerjakan	2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 14:58:18	2026-03-29 14:58:18	\N
287fecc1-663a-49da-8a79-726137aa4831	3b61e2cf-dd4b-4732-ae89-8042450187b2	Selesai	3	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 14:58:18	2026-03-29 14:58:18	\N
1b7785ee-06e4-4d9c-a7b4-1a627d105136	3b61e2cf-dd4b-4732-ae89-8042450187b2	Batal	4	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 14:58:18	2026-03-29 14:58:18	\N
4250e56c-360e-4fbc-bda7-e31d7ed0dea8	07bf3e5b-7d1e-409b-8e32-9df905b7b57b	To Do List	1	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 14:58:26	2026-03-29 14:58:26	\N
fd9ee0d6-9b31-4c3c-8c76-ffc4c1bc9357	07bf3e5b-7d1e-409b-8e32-9df905b7b57b	Dikerjakan	2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 14:58:26	2026-03-29 14:58:26	\N
4188614e-2270-4e1d-b118-b4ea6f74fccc	07bf3e5b-7d1e-409b-8e32-9df905b7b57b	Selesai	3	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 14:58:26	2026-03-29 14:58:26	\N
cb5ac49e-4765-4ad2-8af0-de19c7233355	07bf3e5b-7d1e-409b-8e32-9df905b7b57b	Batal	4	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 14:58:26	2026-03-29 14:58:26	\N
ef00e611-9ec9-4bd9-b505-4b168c73d11f	173a617f-c955-42f6-9042-9815a0553ae6	To Do List	1	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 16:25:50	2026-03-29 16:25:50	\N
e1481d0b-9356-4ec8-bcef-27dbc3b5ef68	173a617f-c955-42f6-9042-9815a0553ae6	Dikerjakan	2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 16:25:50	2026-03-29 16:25:50	\N
ca50d7e9-6f3a-4429-8798-974617a7c5c7	173a617f-c955-42f6-9042-9815a0553ae6	Selesai	3	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 16:25:50	2026-03-29 16:25:50	\N
d80bc8ec-dded-4921-9f29-1e6540f28454	173a617f-c955-42f6-9042-9815a0553ae6	Batal	4	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 16:25:50	2026-03-29 16:25:50	\N
4261bda6-0831-4415-9698-47e9a4deecf4	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	To Do List	1	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 16:33:56	2026-03-29 16:33:56	\N
39e5df8e-6792-4761-9d0b-0a855d8f51b6	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	Dikerjakan	2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 16:33:56	2026-03-29 16:33:56	\N
5d67cc85-b038-4ab4-a819-8bd17902ac21	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	Selesai	3	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 16:33:56	2026-03-29 16:33:56	\N
bb55d2cb-3595-46e2-aec4-1132ff25c1b3	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	Batal	4	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 16:33:56	2026-03-29 16:33:56	\N
6943af26-5148-4fb2-8832-e36af8fa0b8f	f925311c-f164-4133-8014-2de78bdebaec	To Do List	1	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-04 11:07:52	2026-04-04 11:07:52	\N
347b9e83-b716-4cf5-a237-97573ddb3433	f925311c-f164-4133-8014-2de78bdebaec	Dikerjakan	2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-04 11:07:52	2026-04-04 11:07:52	\N
df8cb624-13dd-48d5-af50-5847abaa4a37	f925311c-f164-4133-8014-2de78bdebaec	Selesai	3	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-04 11:07:52	2026-04-04 11:07:52	\N
d82bd33b-fc44-4b8b-9f27-eca67529af8b	f925311c-f164-4133-8014-2de78bdebaec	Batal	4	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-04 11:07:52	2026-04-04 11:07:52	\N
53b75417-68dd-4155-af73-bbe0d36fbac8	a9a33d65-19f4-4aed-b677-934bb14721e4	To Do List	1	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-04 12:48:40	2026-04-04 12:48:40	\N
75a6c096-5114-42b4-995a-df59c871ff85	a9a33d65-19f4-4aed-b677-934bb14721e4	Dikerjakan	2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-04 12:48:40	2026-04-04 12:48:40	\N
bf8e6431-a559-42a9-b594-a3d6d9abeed4	a9a33d65-19f4-4aed-b677-934bb14721e4	Selesai	3	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-04 12:48:40	2026-04-04 12:48:40	\N
1fda8285-0996-4b12-8521-edabe17d7671	a9a33d65-19f4-4aed-b677-934bb14721e4	Batal	4	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-04 12:48:40	2026-04-04 12:48:40	\N
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-board_columns_70ea9ff4-aa59-4cf5-b8cf-3376a201918b	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImQ0ZjQ2NjMwLWM5ZTgtNDk0Yi1iZjQ1LThiNDliZDFhMTM2NCI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiI3MGVhOWZmNC1hYTU5LTRjZjUtYjhjZi0zMzc2YTIwMTkxOGIiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiJkNGY0NjYzMC1jOWU4LTQ5NGItYmY0NS04YjQ5YmQxYTEzNjQiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiNzBlYTlmZjQtYWE1OS00Y2Y1LWI4Y2YtMzM3NmEyMDE5MThiIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImE4NzJmNTM3LTZjMDAtNGE0YS05YmU2LWU3MjNiZmNhZTVkMSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjcwZWE5ZmY0LWFhNTktNGNmNS1iOGNmLTMzNzZhMjAxOTE4YiI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImE4NzJmNTM3LTZjMDAtNGE0YS05YmU2LWU3MjNiZmNhZTVkMSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjcwZWE5ZmY0LWFhNTktNGNmNS1iOGNmLTMzNzZhMjAxOTE4YiI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1773298833
laravel-cache-board_columns_325797d8-e3ad-4e66-a280-a8098d195bc8	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OjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImM3MDQ1OWQzLWMyMWItNGUxZC1iZmQ4LWU1ZmMwNjBkOTg0MCI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjMyNTc5N2Q4LWUzYWQtNGU2Ni1hMjgwLWE4MDk4ZDE5NWJjOCI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImM3MDQ1OWQzLWMyMWItNGUxZC1iZmQ4LWU1ZmMwNjBkOTg0MCI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjMyNTc5N2Q4LWUzYWQtNGU2Ni1hMjgwLWE4MDk4ZDE5NWJjOCI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1773590642
laravel-cache-board_columns_8e26fb16-12bc-4768-b8b6-72f7d28efcc8	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjM5ZTVkZjhlLTY3OTItNDc2MS05ZDBiLTBhODU1ZDhmNTFiNiI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiI4ZTI2ZmIxNi0xMmJjLTQ3NjgtYjhiNi03MmY3ZDI4ZWZjYzgiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiIzOWU1ZGY4ZS02NzkyLTQ3NjEtOWQwYi0wYTg1NWQ4ZjUxYjYiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiOGUyNmZiMTYtMTJiYy00NzY4LWI4YjYtNzJmN2QyOGVmY2M4Ijt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjVkNjdjYzg1LWIwMzgtNGFiNC1hODE5LThiZDE3OTAyYWMyMSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjhlMjZmYjE2LTEyYmMtNDc2OC1iOGI2LTcyZjdkMjhlZmNjOCI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjVkNjdjYzg1LWIwMzgtNGFiNC1hODE5LThiZDE3OTAyYWMyMSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjhlMjZmYjE2LTEyYmMtNDc2OC1iOGI2LTcyZjdkMjhlZmNjOCI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2VzVW5pcXVlSWRzIjtiOjA7czo5OiIAKgBoaWRkZW4iO2E6MDp7fXM6MTA6IgAqAHZpc2libGUiO2E6MDp7fXM6MTE6IgAqAGZpbGxhYmxlIjthOjU6e2k6MDtzOjI6ImlkIjtpOjE7czoxMjoid29ya3NwYWNlX2lkIjtpOjI7czo0OiJuYW1lIjtpOjM7czo4OiJwb3NpdGlvbiI7aTo0O3M6MTA6ImNyZWF0ZWRfYnkiO31zOjEwOiIAKgBndWFyZGVkIjthOjE6e2k6MDtzOjE6IioiO31zOjg6IgAqAGRhdGVzIjthOjE6e2k6MDtzOjEwOiJkZWxldGVkX2F0Ijt9czoxNjoiACoAZm9yY2VEZWxldGluZyI7YjowO31pOjM7TzoyMjoiQXBwXE1vZGVsc1xCb2FyZENvbHVtbiI6MzU6e3M6MTM6IgAqAGNvbm5lY3Rpb24iO3M6NToicGdzcWwiO3M6ODoiACoAdGFibGUiO3M6MTM6ImJvYXJkX2NvbHVtbnMiO3M6MTM6IgAqAHByaW1hcnlLZXkiO3M6MjoiaWQiO3M6MTA6IgAqAGtleVR5cGUiO3M6Njoic3RyaW5nIjtzOjEyOiJpbmNyZW1lbnRpbmciO2I6MDtzOjc6IgAqAHdpdGgiO2E6MDp7fXM6MTI6IgAqAHdpdGhDb3VudCI7YTowOnt9czoxOToicHJldmVudHNMYXp5TG9hZGluZyI7YjowO3M6MTA6IgAqAHBlclBhZ2UiO2k6MTU7czo2OiJleGlzdHMiO2I6MTtzOjE4OiJ3YXNSZWNlbnRseUNyZWF0ZWQiO2I6MDtzOjI4OiIAKgBlc2NhcGVXaGVuQ2FzdGluZ1RvU3RyaW5nIjtiOjA7czoxMzoiACoAYXR0cmlidXRlcyI7YTo0OntzOjI6ImlkIjtzOjM2OiJiYjU1ZDJjYi0zNTk1LTQ2ZTItYWVjNC0xMTMyZmYyNWMxYjMiO3M6NDoibmFtZSI7czo1OiJCYXRhbCI7czo4OiJwb3NpdGlvbiI7aTo0O3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiOGUyNmZiMTYtMTJiYy00NzY4LWI4YjYtNzJmN2QyOGVmY2M4Ijt9czoxMToiACoAb3JpZ2luYWwiO2E6NDp7czoyOiJpZCI7czozNjoiYmI1NWQyY2ItMzU5NS00NmUyLWFlYzQtMTEzMmZmMjVjMWIzIjtzOjQ6Im5hbWUiO3M6NToiQmF0YWwiO3M6ODoicG9zaXRpb24iO2k6NDtzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjhlMjZmYjE2LTEyYmMtNDc2OC1iOGI2LTcyZjdkMjhlZmNjOCI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2VzVW5pcXVlSWRzIjtiOjA7czo5OiIAKgBoaWRkZW4iO2E6MDp7fXM6MTA6IgAqAHZpc2libGUiO2E6MDp7fXM6MTE6IgAqAGZpbGxhYmxlIjthOjU6e2k6MDtzOjI6ImlkIjtpOjE7czoxMjoid29ya3NwYWNlX2lkIjtpOjI7czo0OiJuYW1lIjtpOjM7czo4OiJwb3NpdGlvbiI7aTo0O3M6MTA6ImNyZWF0ZWRfYnkiO31zOjEwOiIAKgBndWFyZGVkIjthOjE6e2k6MDtzOjE6IioiO31zOjg6IgAqAGRhdGVzIjthOjE6e2k6MDtzOjEwOiJkZWxldGVkX2F0Ijt9czoxNjoiACoAZm9yY2VEZWxldGluZyI7YjowO319czoyODoiACoAZXNjYXBlV2hlbkNhc3RpbmdUb1N0cmluZyI7YjowO30=	1774778329
laravel-cache-board_columns_173a617f-c955-42f6-9042-9815a0553ae6	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImUxNDgxZDBiLTkzNTYtNGVjOC1iY2VmLTI3ZGJjM2I1ZWY2OCI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiIxNzNhNjE3Zi1jOTU1LTQyZjYtOTA0Mi05ODE1YTA1NTNhZTYiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiJlMTQ4MWQwYi05MzU2LTRlYzgtYmNlZi0yN2RiYzNiNWVmNjgiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiMTczYTYxN2YtYzk1NS00MmY2LTkwNDItOTgxNWEwNTUzYWU2Ijt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImNhNTBkN2U5LTZmM2EtNDQyOS04Nzk4LTk3NDYxN2E3YzVjNyI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjE3M2E2MTdmLWM5NTUtNDJmNi05MDQyLTk4MTVhMDU1M2FlNiI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImNhNTBkN2U5LTZmM2EtNDQyOS04Nzk4LTk3NDYxN2E3YzVjNyI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjE3M2E2MTdmLWM5NTUtNDJmNi05MDQyLTk4MTVhMDU1M2FlNiI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1774778502
laravel-cache-board_columns_3b61e2cf-dd4b-4732-ae89-8042450187b2	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjU5MTBiMzlkLTVhZDctNGJlOS04YjYxLTI2YzkwN2M4ZTFiMSI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiIzYjYxZTJjZi1kZDRiLTQ3MzItYWU4OS04MDQyNDUwMTg3YjIiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiI1OTEwYjM5ZC01YWQ3LTRiZTktOGI2MS0yNmM5MDdjOGUxYjEiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiM2I2MWUyY2YtZGQ0Yi00NzMyLWFlODktODA0MjQ1MDE4N2IyIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjI4N2ZlY2MxLTY2M2EtNDlkYS04YTc5LTcyNjEzN2FhNDgzMSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjNiNjFlMmNmLWRkNGItNDczMi1hZTg5LTgwNDI0NTAxODdiMiI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjI4N2ZlY2MxLTY2M2EtNDlkYS04YTc5LTcyNjEzN2FhNDgzMSI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6IjNiNjFlMmNmLWRkNGItNDczMi1hZTg5LTgwNDI0NTAxODdiMiI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1775113341
laravel-cache-board_columns_f925311c-f164-4133-8014-2de78bdebaec	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6IjM0N2I5ZTgzLWI3MTYtNGNmNS1hMjM3LTk3NTczZGRiMzQzMyI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiJmOTI1MzExYy1mMTY0LTQxMzMtODAxNC0yZGU3OGJkZWJhZWMiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiIzNDdiOWU4My1iNzE2LTRjZjUtYTIzNy05NzU3M2RkYjM0MzMiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiZjkyNTMxMWMtZjE2NC00MTMzLTgwMTQtMmRlNzhiZGViYWVjIjt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImRmOGNiNjI0LTEzZGQtNDhkNS1hZjUwLTU4NDdhYmFhNGEzNyI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImY5MjUzMTFjLWYxNjQtNDEzMy04MDE0LTJkZTc4YmRlYmFlYyI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImRmOGNiNjI0LTEzZGQtNDhkNS1hZjUwLTU4NDdhYmFhNGEzNyI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImY5MjUzMTFjLWYxNjQtNDEzMy04MDE0LTJkZTc4YmRlYmFlYyI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1775282024
laravel-cache-board_columns_a9a33d65-19f4-4aed-b677-934bb14721e4	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czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6Ijc1YTZjMDk2LTUxMTQtNDJiNC05OTVhLWRmNTljODcxZmY4NSI7czo0OiJuYW1lIjtzOjEwOiJEaWtlcmpha2FuIjtzOjg6InBvc2l0aW9uIjtpOjI7czoxMjoid29ya3NwYWNlX2lkIjtzOjM2OiJhOWEzM2Q2NS0xOWY0LTRhZWQtYjY3Ny05MzRiYjE0NzIxZTQiO31zOjExOiIAKgBvcmlnaW5hbCI7YTo0OntzOjI6ImlkIjtzOjM2OiI3NWE2YzA5Ni01MTE0LTQyYjQtOTk1YS1kZjU5Yzg3MWZmODUiO3M6NDoibmFtZSI7czoxMDoiRGlrZXJqYWthbiI7czo4OiJwb3NpdGlvbiI7aToyO3M6MTI6IndvcmtzcGFjZV9pZCI7czozNjoiYTlhMzNkNjUtMTlmNC00YWVkLWI2NzctOTM0YmIxNDcyMWU0Ijt9czoxMDoiACoAY2hhbmdlcyI7YTowOnt9czoxMToiACoAcHJldmlvdXMiO2E6MDp7fXM6ODoiACoAY2FzdHMiO2E6NDp7czo4OiJwb3NpdGlvbiI7czo3OiJpbnRlZ2VyIjtzOjEwOiJjcmVhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJ1cGRhdGVkX2F0IjtzOjg6ImRhdGV0aW1lIjtzOjEwOiJkZWxldGVkX2F0IjtzOjg6ImRhdGV0aW1lIjt9czoxNzoiACoAY2xhc3NDYXN0Q2FjaGUiO2E6MDp7fXM6MjE6IgAqAGF0dHJpYnV0ZUNhc3RDYWNoZSI7YTowOnt9czoxMzoiACoAZGF0ZUZvcm1hdCI7TjtzOjEwOiIAKgBhcHBlbmRzIjthOjA6e31zOjE5OiIAKgBkaXNwYXRjaGVzRXZlbnRzIjthOjA6e31zOjE0OiIAKgBvYnNlcnZhYmxlcyI7YTowOnt9czoxMjoiACoAcmVsYXRpb25zIjthOjA6e31zOjEwOiIAKgB0b3VjaGVzIjthOjA6e31zOjI3OiIAKgByZWxhdGlvbkF1dG9sb2FkQ2FsbGJhY2siO047czoyNjoiACoAcmVsYXRpb25BdXRvbG9hZENvbnRleHQiO047czoxMDoidGltZXN0YW1wcyI7YjoxO3M6MTM6InVzZXNVbmlxdWVJZHMiO2I6MDtzOjk6IgAqAGhpZGRlbiI7YTowOnt9czoxMDoiACoAdmlzaWJsZSI7YTowOnt9czoxMToiACoAZmlsbGFibGUiO2E6NTp7aTowO3M6MjoiaWQiO2k6MTtzOjEyOiJ3b3Jrc3BhY2VfaWQiO2k6MjtzOjQ6Im5hbWUiO2k6MztzOjg6InBvc2l0aW9uIjtpOjQ7czoxMDoiY3JlYXRlZF9ieSI7fXM6MTA6IgAqAGd1YXJkZWQiO2E6MTp7aTowO3M6MToiKiI7fXM6ODoiACoAZGF0ZXMiO2E6MTp7aTowO3M6MTA6ImRlbGV0ZWRfYXQiO31zOjE2OiIAKgBmb3JjZURlbGV0aW5nIjtiOjA7fWk6MjtPOjIyOiJBcHBcTW9kZWxzXEJvYXJkQ29sdW1uIjozNTp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJwZ3NxbCI7czo4OiIAKgB0YWJsZSI7czoxMzoiYm9hcmRfY29sdW1ucyI7czoxMzoiACoAcHJpbWFyeUtleSI7czoyOiJpZCI7czoxMDoiACoAa2V5VHlwZSI7czo2OiJzdHJpbmciO3M6MTI6ImluY3JlbWVudGluZyI7YjowO3M6NzoiACoAd2l0aCI7YTowOnt9czoxMjoiACoAd2l0aENvdW50IjthOjA6e31zOjE5OiJwcmV2ZW50c0xhenlMb2FkaW5nIjtiOjA7czoxMDoiACoAcGVyUGFnZSI7aToxNTtzOjY6ImV4aXN0cyI7YjoxO3M6MTg6Indhc1JlY2VudGx5Q3JlYXRlZCI7YjowO3M6Mjg6IgAqAGVzY2FwZVdoZW5DYXN0aW5nVG9TdHJpbmciO2I6MDtzOjEzOiIAKgBhdHRyaWJ1dGVzIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImJmOGU2NDMxLWE1NTktNDJhOS1iNTk0LWEzZDZkOWFiZWVkNCI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImE5YTMzZDY1LTE5ZjQtNGFlZC1iNjc3LTkzNGJiMTQ3MjFlNCI7fXM6MTE6IgAqAG9yaWdpbmFsIjthOjQ6e3M6MjoiaWQiO3M6MzY6ImJmOGU2NDMxLWE1NTktNDJhOS1iNTk0LWEzZDZkOWFiZWVkNCI7czo0OiJuYW1lIjtzOjc6IlNlbGVzYWkiO3M6ODoicG9zaXRpb24iO2k6MztzOjEyOiJ3b3Jrc3BhY2VfaWQiO3M6MzY6ImE5YTMzZDY1LTE5ZjQtNGFlZC1iNjc3LTkzNGJiMTQ3MjFlNCI7fXM6MTA6IgAqAGNoYW5nZXMiO2E6MDp7fXM6MTE6IgAqAHByZXZpb3VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjQ6e3M6ODoicG9zaXRpb24iO3M6NzoiaW50ZWdlciI7czoxMDoiY3JlYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoidXBkYXRlZF9hdCI7czo4OiJkYXRldGltZSI7czoxMDoiZGVsZXRlZF9hdCI7czo4OiJkYXRldGltZSI7fXM6MTc6IgAqAGNsYXNzQ2FzdENhY2hlIjthOjA6e31zOjIxOiIAKgBhdHRyaWJ1dGVDYXN0Q2FjaGUiO2E6MDp7fXM6MTM6IgAqAGRhdGVGb3JtYXQiO047czoxMDoiACoAYXBwZW5kcyI7YTowOnt9czoxOToiACoAZGlzcGF0Y2hlc0V2ZW50cyI7YTowOnt9czoxNDoiACoAb2JzZXJ2YWJsZXMiO2E6MDp7fXM6MTI6IgAqAHJlbGF0aW9ucyI7YTowOnt9czoxMDoiACoAdG91Y2hlcyI7YTowOnt9czoyNzoiACoAcmVsYXRpb25BdXRvbG9hZENhbGxiYWNrIjtOO3M6MjY6IgAqAHJlbGF0aW9uQXV0b2xvYWRDb250ZXh0IjtOO3M6MTA6InRpbWVzdGFtcHMiO2I6MTtzOjEzOiJ1c2Vz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	1775282052
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: calendar_events; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calendar_events (id, workspace_id, created_by, title, description, start_datetime, end_datetime, recurrence, is_private, is_online_meeting, meeting_link, created_at, updated_at, deleted_at, company_id, location) FROM stdin;
\.


--
-- Data for Name: calendar_participants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calendar_participants (id, event_id, user_id, status, attendance) FROM stdin;
\.


--
-- Data for Name: checklists; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.checklists (id, task_id, title, is_done, created_at, updated_at, "position") FROM stdin;
\.


--
-- Data for Name: colors; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.colors (id, rgb) FROM stdin;
2a854d03-557a-4457-b395-136d0baafcf5	#795548
2cf4a4f4-06a0-47c7-876b-2cf788e16351	#FF9800
34baa825-01f9-4b26-8fc4-519dfca5af6b	#9E9E9E
3f9fedb9-d632-4a8f-860a-05e0781fb70c	#000000
4712cec0-289c-44bd-9592-7bdaa5dbe883	#00796B
4ce6f29c-fbf7-4ade-b6a3-4e49ac28dec3	#8BC34A
5c0a1142-25ef-4373-9164-6137718a5b5f	#FFFFFF
62f25f1b-506e-4f9e-a0f5-bac0bc6d543d	#FF5722
69bed3f4-1659-49c1-8bbe-820e135f781f	#FF4C4C
6c8f270d-fc3d-45fa-a35a-64be13a797e9	#303F9F
6f736fa7-7b45-4b05-a3d4-cdad4851c02b	#607D8B
75442d05-1b79-446f-ae39-d3698531caa9	#FFC107
780ac390-64ae-4e4c-b599-cd07c0bfe105	#0288D1
792985e0-1d2f-4830-b296-4c06a97a0c64	#7B1FA2
7f07267f-c7cb-430a-b840-4bf68072454b	#3F51B5
7f0e2934-5c76-41b6-b1bd-eb57c5509d53	#00BCD4
828fd126-71c8-4692-b990-a067faa78b2b	#512DA8
97176aa4-a936-42f4-9a67-b07fa555ad87	#C2185B
986e4c53-cf2a-4b8a-89ed-aa4fca0a00fb	#2E7D32
98d8ecc3-3b2c-4e2a-9f54-7138c43534e1	#CDDC39
9ca431e2-97f8-4eba-9656-0286baf96ee9	#4CAF50
a222164c-e36e-43ad-b9bb-6867f94c3f43	#009688
b2ed830a-1187-477c-813f-dbba8cf3114a	#FFEB3B
b7265dd1-078e-4365-a718-4a9736c43698	#E91E63
cee15dee-c5b0-477b-a9ea-d511e64f3b31	#FFCDD2
cfe9ecde-3169-4db8-b535-67ac6d728ff9	#03A9F4
d6d3bbb2-c943-433c-ac71-c451925134f7	#2196F3
f4f8cdec-f51b-439d-a4ca-38a232480fa3	#F44336
fa32a7da-4d6b-4149-ac51-588bb48f6555	#673AB7
fe4955e8-9aad-4677-b0d8-3665c5c84b53	#9C27B0
\.


--
-- Data for Name: comments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.comments (id, parent_comment_id, commentable_type, commentable_id, user_id, content, created_at, updated_at, deleted_at) FROM stdin;
0f8f0a0d-9f6f-43d1-90fc-da53ba3e4973	\N	App\\Models\\File	e644d5da-dae7-4d71-8ee2-a42eb9fdc9f3	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	<p>Sudah saya tambahkan revisi pada SOP pasal 4 ya</p>	2026-03-29 15:10:36	2026-03-29 15:10:36	\N
60c3410e-111e-4f52-826d-75f344aebed9	\N	App\\Models\\File	e121acd6-a612-4807-8b11-36def1e52652	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	<p>sudah saya perbaiki kesalahan kemarin ya</p>	2026-03-29 15:15:49	2026-03-29 15:15:49	\N
75fb001b-9fe0-4c98-9107-c472a6a21c87	\N	App\\Models\\Pengumuman	f014093e-93b3-413e-9655-0b5b1b32b1cf	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	<p>bisa lebih cepat</p>	2026-04-02 16:03:17	2026-04-02 16:04:02	2026-04-02 16:04:02
e31f6c4c-185c-48cc-aca3-03a302356cd7	\N	App\\Models\\Pengumuman	228e6dcf-4b79-4a80-b95d-fd4acd30eb5b	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	<p>jangan lama ya</p>	2026-04-02 16:07:11	2026-04-02 16:07:11	\N
205fa5a6-d9ff-4540-8a96-a74d2516639e	\N	App\\Models\\Pengumuman	d6ac3bdc-7dec-4caa-882b-8dd1352774e9	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	<p>oh di ubah lagi ya</p>	2026-04-02 16:09:57	2026-04-02 16:09:57	\N
cf2151ee-cc3f-4f25-9a75-35b46dab0fc5	\N	App\\Models\\Pengumuman	6ce8e396-7497-498d-bb0a-a607d337fffd	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	<p>oke di tunggu</p>	2026-04-02 16:50:49	2026-04-02 16:50:49	\N
\.


--
-- Data for Name: companies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.companies (id, name, email, address, phone, created_at, updated_at, deleted_at, trial_start, trial_end, status) FROM stdin;
a55a03f3-2191-4b53-833c-d7de8ce62c9b	Kuliah	\N	\N	\N	2025-12-28 13:16:28	2025-12-28 13:16:28	\N	2025-12-28 13:16:28	2026-01-04 13:16:28	trial
ce50b57e-01c9-4cbf-a4d0-dd57c81939ad	kk	\N	\N	\N	2026-03-29 12:27:25	2026-03-29 12:27:25	\N	2026-03-29 12:27:25	2026-04-05 12:27:25	trial
31c7b915-01ea-40ed-80be-723ffe01c10d	Bismillah	\N	\N	\N	2026-03-29 13:24:43	2026-03-29 13:24:43	\N	2026-03-29 13:24:43	2026-04-05 13:24:43	trial
5f00c14f-d22b-43ca-bb62-6ec96c171167	Alhamdulillah	\N	\N	\N	2026-03-29 14:31:43	2026-03-29 14:31:43	\N	2026-03-29 14:31:43	2026-04-05 14:31:43	trial
f4c42d62-601d-4061-8553-70a6c7171a77	Our Forever Story	\N	\N	\N	2026-03-29 14:37:05	2026-03-29 14:37:05	\N	2026-03-29 14:37:05	2026-04-05 14:37:05	trial
705411fd-87ba-4a7f-8e4c-e5034b420ed4	Perusahaan Keikanan	\N	\N	\N	2026-04-01 13:50:56	2026-04-01 13:52:00	\N	2026-04-01 13:50:56	2026-04-08 13:50:56	trial
6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	suki	\N	\N	\N	2026-03-08 02:17:50	2026-04-02 15:06:58	2026-04-02 15:06:58	2026-03-08 02:17:50	2026-03-15 02:17:50	expired
e09653fc-2551-4657-a1db-13d07034b214	aaa	\N	\N	\N	2026-04-03 04:10:28	2026-04-03 04:10:32	2026-04-03 04:10:32	2026-04-03 04:10:28	2026-04-10 04:10:28	trial
1ae3fbab-0daf-4bc0-b65e-664ffa7618ed	safd	\N	\N	\N	2026-04-03 04:10:38	2026-04-03 04:10:46	2026-04-03 04:10:46	2026-04-03 04:10:38	2026-04-10 04:10:38	trial
31e35745-9e5b-4487-93f0-0ceec9d3229a	asdfasdf	\N	\N	\N	2026-04-03 04:10:51	2026-04-03 04:10:56	2026-04-03 04:10:56	2026-04-03 04:10:51	2026-04-10 04:10:51	trial
24b1d146-d8c8-49c5-841d-a4a4bed53abe	Bis	\N	\N	\N	2026-04-03 04:10:58	2026-04-03 04:17:38	2026-04-03 04:17:38	2026-04-03 04:10:58	2026-04-10 04:10:58	trial
da242fb4-a281-4339-a8a4-9a66436e237d	ljj	\N	\N	\N	2026-03-12 12:46:31	2026-04-04 10:57:41	\N	2026-03-12 12:46:31	2026-03-19 12:46:31	expired
aed661f0-a039-4927-8259-6ea71f9943f7	nnn	\N	\N	\N	2026-03-28 15:16:56	2026-04-04 16:44:37	\N	2026-03-28 15:16:56	2026-04-04 15:16:56	expired
\.


--
-- Data for Name: conversation_participants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.conversation_participants (id, conversation_id, user_id, joined_at, is_admin, last_read_at) FROM stdin;
6274ab64-fad0-4ba1-a66b-f83ca53d33cc	249b9769-918d-4108-a84f-0ee6d5d1dd75	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-12 08:32:52.833459	f	\N
0fbe1d38-b267-46fa-b703-c609ea525ec3	452280dc-8d95-449b-be9d-3d98ec13c255	019ce093-8522-725c-8f9c-9b3928ec6ad3	2026-03-15 15:58:04.380916	f	\N
f77583a4-f861-4f9c-80bf-3415bf255858	50dd6bef-4c00-4a06-ba99-ad2c0a7496be	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-02 09:46:17.474122	f	\N
\.


--
-- Data for Name: conversations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.conversations (id, workspace_id, created_at, type, name, created_by, updated_at, last_message_id, scope, company_id) FROM stdin;
249b9769-918d-4108-a84f-0ee6d5d1dd75	\N	2026-03-12 15:32:52	group	suki	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-12 15:32:52	\N	company	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433
452280dc-8d95-449b-be9d-3d98ec13c255	325797d8-e3ad-4e66-a280-a8098d195bc8	2026-03-15 22:58:04	group	ss	019ce093-8522-725c-8f9c-9b3928ec6ad3	2026-03-15 22:58:04	\N	workspace	\N
50dd6bef-4c00-4a06-ba99-ad2c0a7496be	\N	2026-04-02 16:46:17	group	Bismillah	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-02 16:46:17	\N	company	31c7b915-01ea-40ed-80be-723ffe01c10d
\.


--
-- Data for Name: document_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.document_recipients (id, document_id, user_id, status, created_at, updated_at) FROM stdin;
ab16c2dd-a5ce-44b7-89ad-6c42ae073514	b655b44d-7a1c-4868-94c3-48106a7679cf	019d3837-daba-7266-8af4-3cedf35e27da	t	2026-03-29 15:16:37	2026-03-29 15:16:37
ae00ca86-b11b-46e5-8ba1-5e3831f59224	b655b44d-7a1c-4868-94c3-48106a7679cf	019d386f-efdf-720e-badc-4570a7ca3df8	t	2026-03-29 15:16:37	2026-03-29 15:16:37
\.


--
-- Data for Name: feedbacks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.feedbacks (id, name, email, message, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: files; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.files (id, folder_id, workspace_id, file_url, is_private, uploaded_by, uploaded_at, file_name, file_path, file_size, file_type, company_id, preview_image_url) FROM stdin;
13fb32aa-5bde-4511-8200-d7bf95cc4d82	\N	\N	http://localhost:8000/storage/files/—Pngtree—ring bell notification icon_8517517.png	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 02:19:00	—Pngtree—ring bell notification icon_8517517.png	files/—Pngtree—ring bell notification icon_8517517.png	1410819	png	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	\N
48526ac7-3833-4306-8080-023144e86f43	\N	\N	http://localhost:8000/storage/files/_ (7).jpeg	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 02:49:03	_ (7).jpeg	files/_ (7).jpeg	52539	jpeg	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	\N
497f960e-9636-4586-8a0a-076ff78419cd	\N	\N	http://localhost:8000/storage/files/52258c5d-7db4-4df6-9042-4fe0f279ac52_removalai_preview.png	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 02:55:39	52258c5d-7db4-4df6-9042-4fe0f279ac52_removalai_preview.png	files/52258c5d-7db4-4df6-9042-4fe0f279ac52_removalai_preview.png	260767	png	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	\N
76b4c6c3-cbd8-47df-8477-21acf409410d	\N	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	https://youtu.be/m4E3srBfoLY?si=Tx1DXMC3LimIyOQo	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 02:42:59	28 Menit Memahami Semua Aliran Dalam Islam	\N	0	Link	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	https://i.ytimg.com/vi/m4E3srBfoLY/hqdefault.jpg
f537d206-a1b6-4d40-9232-841f0c3b2022	ff4c8e0c-7678-4241-8ac7-ea8d91413d37	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	https://youtu.be/m4E3srBfoLY?si=Tx1DXMC3LimIyOQo	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 02:33:00	28 Menit Memahami Semua Aliran Dalam Islam	\N	0	Link	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	https://i.ytimg.com/vi/m4E3srBfoLY/hqdefault.jpg
46748a2f-2fe9-471a-a407-cade30e1bef3	ff4c8e0c-7678-4241-8ac7-ea8d91413d37	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	https://youtu.be/4tqzyu9D-PA?si=0Sf80SD9ApmKlc3H	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 02:43:30	Tan Malaka dan DN Aidit: Ketika Revolusi Dipahami Berbeda!	\N	0	Link	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	https://i.ytimg.com/vi/4tqzyu9D-PA/hqdefault.jpg
d83bafe2-cc5c-4edb-bf16-e4f3aa7c79c8	ff4c8e0c-7678-4241-8ac7-ea8d91413d37	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	https://www.instagram.com/reel/DVl_km6juEz/?utm_source=ig_web_copy_link&igsh=NTc4MTIwNjQ2YQ==	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 02:46:54	Braedon on Instagram: "Science has always been context dependent @wes1eychandler ￼ - Code “KD” @ekkovision Code “DELTS” @gym_pin - #bodybuilding #lifting #hypertrophy #gym #science"	\N	0	Link	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	https://scontent.cdninstagram.com/v/t51.82787-15/647368450_17894188911423807_396165516216897510_n.jpg?stp=cmp1_dst-jpg_e35_s640x640_tt6&_nc_cat=105&ccb=7-5&_nc_sid=18de74&efg=eyJlZmdfdGFnIjoiQ0xJUFMuYmVzdF9pbWFnZV91cmxnZW4uQzMifQ%3D%3D&_nc_ohc=fAoeVjUJIM4Q7kNvwFZcpK1&_nc_oc=AdmAzf3o5ms5BtIijnlzAizDZM87VnpcCWNGbOHalx2UrZv5nzs1l_jOaWnl3YFJQL8&_nc_zt=23&_nc_ht=scontent.cdninstagram.com&_nc_gid=3meenDtX61TyzWqTZr8ATw&_nc_ss=8&oh=00_Afx32X_2orFNt8Zimza7f_u8NnwuPjzYKKjTm3Zrzv6GZQ&oe=69B24B2A
b137b979-1223-4d75-b2b3-3074ede6911b	ff4c8e0c-7678-4241-8ac7-ea8d91413d37	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	http://localhost:8000/storage/files/—Pngtree—ring bell notification icon_8517517.png	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 02:55:48	—Pngtree—ring bell notification icon_8517517.png	files/—Pngtree—ring bell notification icon_8517517.png	1410819	png	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	\N
e80217a7-0b47-4223-be5c-46645a5090c5	ff4c8e0c-7678-4241-8ac7-ea8d91413d37	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	https://youtu.be/Fm-999-UBRg?si=n0KouK9HcOjGyE9Z	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 16:59:26	『相方スベリ前提ツッコミな奴』ジャルジャルのネタのタネ【JARUJARUTOWER】	\N	0	Link	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	https://i.ytimg.com/vi/Fm-999-UBRg/hqdefault.jpg
48c98813-ee5d-437b-af75-80cb1f953216	\N	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	https://youtu.be/Fm-999-UBRg?si=n0KouK9HcOjGyE9Z	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 16:59:12	『相方スベリ前提ツッコミな奴』ジャルジャルのネタのタネ【JARUJARUTOWER】	\N	0	Link	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	https://i.ytimg.com/vi/Fm-999-UBRg/hqdefault.jpg
9c578e28-4dfb-450e-8720-2c003e3b9470	\N	826f878b-ea65-43cd-923d-531b3ddf4599	https://youtu.be/Z3QkQ0DfOzA?si=HVhIl3q1LWffAW3t	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 17:00:32	会話のリズム合わん奴	\N	0	Link	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	https://i.ytimg.com/vi/Z3QkQ0DfOzA/hqdefault.jpg
56b72c98-40cd-4afa-bd55-cf496f5a6cd0	\N	\N	https://youtu.be/XQ-ITuArBLA?si=bTLg9RGmCh_aFQwz	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 17:06:44	ゆっくり解説 熱帯に先進国が少なく、温帯と冷帯に先進国が多すぎる件について	\N	0	Link	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	https://i.ytimg.com/vi/XQ-ITuArBLA/hqdefault.jpg
b1bca3e4-9c0e-42be-8bf2-ba26fc4f5c14	\N	\N	https://youtu.be/XQ-ITuArBLA?si=bTLg9RGmCh_aFQwz	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 17:06:50	ゆっくり解説 熱帯に先進国が少なく、温帯と冷帯に先進国が多すぎる件について	\N	0	Link	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	https://i.ytimg.com/vi/XQ-ITuArBLA/hqdefault.jpg
139b3c7b-7577-40ed-9670-3a7c60f36f25	\N	\N	http://localhost:8000/storage/files/Tugas3.pdf	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-28 15:25:31	Tugas3.pdf	files/Tugas3.pdf	41379	pdf	aed661f0-a039-4927-8259-6ea71f9943f7	\N
9ce45cdf-a499-463d-8f59-ce2135016e0e	\N	3b61e2cf-dd4b-4732-ae89-8042450187b2	http://localhost:8000/storage/files/Dokumen MOU PT Xyz.pdf	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:03:07	Dokumen MOU PT Xyz.pdf	files/Dokumen MOU PT Xyz.pdf	41379	pdf	\N	\N
11241f7c-bec2-4ca8-b1da-9867e420d174	\N	3b61e2cf-dd4b-4732-ae89-8042450187b2	http://localhost:8000/storage/files/SOP Perusahaan.pptx	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:03:23	SOP Perusahaan.pptx	files/SOP Perusahaan.pptx	65386	pptx	\N	\N
953c92f6-2907-4432-ba24-008c2be8e441	\N	\N	http://localhost:8000/storage/files/Dokumen MOU PT Xyz.pdf	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:08:27	Dokumen MOU PT Xyz.pdf	files/Dokumen MOU PT Xyz.pdf	41379	pdf	31c7b915-01ea-40ed-80be-723ffe01c10d	\N
69aa75e1-64a2-420f-96ab-c237e4f6b08e	\N	\N	https://www.youtube.com/watch?v=C5XAo3Xbvwc&t=94s	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:09:48	Demo Aplikasi Koladi - Program Studi Teknologi Rekayasa Perangkat Lunak - Politeknik Negeri Batam	\N	0	Link	31c7b915-01ea-40ed-80be-723ffe01c10d	https://i.ytimg.com/vi/C5XAo3Xbvwc/hqdefault.jpg
8061fcf9-54be-44c0-877a-75c8c25a468c	\N	3b61e2cf-dd4b-4732-ae89-8042450187b2	http://localhost:8000/storage/files/—Pngtree—ring bell notification icon_8517517.png	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:13:08	—Pngtree—ring bell notification icon_8517517.png	files/—Pngtree—ring bell notification icon_8517517.png	1410819	png	\N	\N
454cf004-de26-432e-a718-2e3680bda348	\N	3b61e2cf-dd4b-4732-ae89-8042450187b2	http://localhost:8000/storage/files/[CITYPNG.COM]HD Red Line Arrow PNG - 1000x1000.png	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:13:08	[CITYPNG.COM]HD Red Line Arrow PNG - 1000x1000.png	files/[CITYPNG.COM]HD Red Line Arrow PNG - 1000x1000.png	46536	png	\N	\N
8d55ccbd-a40b-45de-ba67-6793b0eb4acc	\N	3b61e2cf-dd4b-4732-ae89-8042450187b2	http://localhost:8000/storage/files/06 Surat Pengalihan HKI.docx	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:13:08	06 Surat Pengalihan HKI.docx	files/06 Surat Pengalihan HKI.docx	279005	docx	\N	\N
3db02019-6aff-4458-9dfc-e0293c91f84d	\N	3b61e2cf-dd4b-4732-ae89-8042450187b2	http://localhost:8000/storage/files/072_SPm_Surat Izin Orang Tua Rihlah.pdf	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:13:08	072_SPm_Surat Izin Orang Tua Rihlah.pdf	files/072_SPm_Surat Izin Orang Tua Rihlah.pdf	1130734	pdf	\N	\N
e121acd6-a612-4807-8b11-36def1e52652	\N	\N	http://localhost:8000/storage/files/Laporan Keuangan Q1 2025.pdf	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:14:35	Laporan Keuangan Q1 2025.pdf	files/Laporan Keuangan Q1 2025.pdf	1010183	pdf	31c7b915-01ea-40ed-80be-723ffe01c10d	\N
f703ac9e-054b-4dee-8631-ebb28e08e472	\N	\N	https://www.youtube.com/watch?v=C5XAo3Xbvwc&t=94s	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:14:52	Demo Aplikasi Koladi - Program Studi Teknologi Rekayasa Perangkat Lunak - Politeknik Negeri Batam	\N	0	Link	31c7b915-01ea-40ed-80be-723ffe01c10d	https://i.ytimg.com/vi/C5XAo3Xbvwc/hqdefault.jpg
b655b44d-7a1c-4868-94c3-48106a7679cf	\N	3b61e2cf-dd4b-4732-ae89-8042450187b2	http://localhost:8000/storage/files/Laporan Keuangan Q1 2025.pdf	t	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:03:23	Laporan Keuangan Q1 2025.pdf	files/Laporan Keuangan Q1 2025.pdf	1010183	pdf	\N	\N
0e310f89-7f78-4115-9312-796a01b8a825	\N	\N	https://www.youtube.com/watch?v=QCdmBua4bms	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-04 10:46:58	الحمدلله الذي خلق السماوات والأرض وجعل الظلمات والنور [محمد ايوب]أجمل التلاوات الخاشعة(سورة الأنعام)	\N	0	Link	aed661f0-a039-4927-8259-6ea71f9943f7	https://i.ytimg.com/vi/QCdmBua4bms/hqdefault.jpg
\.


--
-- Data for Name: folders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.folders (id, workspace_id, name, is_private, created_by, created_at, updated_at, deleted_at, parent_id, company_id) FROM stdin;
ff4c8e0c-7678-4241-8ac7-ea8d91413d37	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	kjh	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 02:32:49	2026-03-08 10:31:02	\N	\N	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433
b0cd7806-5e61-4399-8b58-bfa9c1e65d72	\N	Folder 2	f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 15:14:17	2026-03-29 15:14:17	\N	\N	31c7b915-01ea-40ed-80be-723ffe01c10d
\.


--
-- Data for Name: insight_recipients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.insight_recipients (id, insight_id, user_id) FROM stdin;
\.


--
-- Data for Name: insights; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.insights (id, workspace_id, created_by, description, delivery_days, delivery_time, is_private, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: invitations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.invitations (id, email_target, token, status, invited_by, company_id, created_at, expired_at, updated_at) FROM stdin;
d356771c-7977-416d-b15b-c566d1d7209a	naufal201080@gmail.com	KYLvZ3MUMv1kMOluLMhkCTF53DHbbHR1KiwEZXlYwhwBCIVh218j3FoZCqA6mGPH	accepted	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	aed661f0-a039-4927-8259-6ea71f9943f7	2026-03-29 12:12:22	2026-04-01 12:12:22	2026-03-29 05:20:02.609447
4ee41c80-ce1c-4386-bb6a-183da32f4aff	wixaya4731@jsncos.com	RrLJv2zcRj5qyJPHfRKC5BsjRGj8gE3TVvMWOnyIosFNe3E7K3jaTFXGUH1I7aHE	pending	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	aed661f0-a039-4927-8259-6ea71f9943f7	2026-03-29 13:09:57	2026-04-01 13:09:57	2026-03-29 13:09:57
85c4c99e-552e-442d-af44-4097aaba117f	medeso9204@smkanba.com	4ZWFahzQfywiQthyzbOvgMFi26eALmJNUCh0O6PqCaEbdiXF8jKKUituyIPaA7Hv	pending	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	aed661f0-a039-4927-8259-6ea71f9943f7	2026-03-29 13:09:57	2026-04-01 13:09:57	2026-03-29 13:09:57
5acfe28c-52cd-4757-9743-4964ab1ae902	kukvu39fzu@lnovic.com	P7ksU2BlfdmJ1ZsghN0vEUiOLIEDMBvYanI1RkcGdbpnYH4CZjZul04kxyIxvjw4	accepted	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	aed661f0-a039-4927-8259-6ea71f9943f7	2026-03-29 13:09:57	2026-04-01 13:09:57	2026-03-29 06:11:32.49285
4efc7adb-6183-4b15-bbcc-2b62f97c370f	kukvu39fzu@lnovic.com	L1S2dAYHzE9eXnhfaLRZMxYvT5rcx00RPWhkOyNhsrVN5sFIWu4j3BejRY6Urj6Q	accepted	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	31c7b915-01ea-40ed-80be-723ffe01c10d	2026-03-29 13:24:56	2026-04-01 13:24:56	2026-03-29 06:25:09.325802
903ddc72-e156-4eb2-9daf-877fadfa6bca	hexaxel671@izkat.com	OIZ4gIbkdF7CcHWpkvQ8BXp5ZhJFzBwu5fEOrBYx96Owkeey6YVuYobREnztzVsh	pending	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	31c7b915-01ea-40ed-80be-723ffe01c10d	2026-03-29 13:54:40	2026-04-01 13:54:40	2026-03-29 13:54:40
22b2fb0c-f3db-4c09-8701-41466cd61d42	lefoxik200@smkanba.com	DKpJ2K56qNuDDOYfhjafdCuoDD3OIwzV5EarWewvvOLKYom7BAz1KlVh3NQBPz3Z	accepted	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	31c7b915-01ea-40ed-80be-723ffe01c10d	2026-03-29 13:54:40	2026-04-01 13:54:40	2026-03-29 07:05:52.64129
e4158deb-b467-4958-9163-cafc2a8cc13a	cedaf73762@muncloud.com	lukn620xSgbnbNB3GIjiSdPgJfE8MPVd1Rxnp6iTakXyxY5BC8bgyzLTYqw6kh9m	accepted	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	31c7b915-01ea-40ed-80be-723ffe01c10d	2026-03-29 14:11:22	2026-04-01 14:11:22	2026-03-29 07:12:40.558167
3a42e8f7-8452-4262-82fb-97b816fc0952	krii5v4jvq@ozsaip.com	NJyUcBYytEOra1gBYnkvKMUhgiHpzTjM2GcivEYf2TI3PyrEBsQwmXbvF29J6dIs	accepted	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	31c7b915-01ea-40ed-80be-723ffe01c10d	2026-03-29 14:23:27	2026-04-01 14:23:27	2026-03-29 07:25:17.449626
1fa42ecd-aaef-4dc6-ad5b-293154a6efcc	9uln0hwmrm@bwmyga.com	0NFm7BRJkAmP16qkHaj9LDD2MeX7GqGTKZbMd3CpjNWnhKIWNMW0R39wtjKpdwzC	accepted	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	31c7b915-01ea-40ed-80be-723ffe01c10d	2026-03-29 14:23:27	2026-04-01 14:23:27	2026-03-29 07:28:08.56385
e1705d8b-b358-47ab-8dd9-5b6a32b0c9b1	q0qy6ex4on@bwmyga.com	NMU00yQqoquvqR2AMflBHAB1OaE9TcHM3Z9WWiNRr3LZtdBdRlENIElm9Tt0WriU	accepted	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	31c7b915-01ea-40ed-80be-723ffe01c10d	2026-03-29 14:34:16	2026-04-01 14:34:16	2026-03-29 07:35:48.514963
fb9ae0d8-d1e7-4efb-905b-d7f46eb944a0	ardhaniishere@gmail.com	Gl9MS3u3UX17i3OMOo5epEjVyaskrvMMtTGXbpFbiJCahTZIyyharR2470SwFkby	accepted	019d3881-49e4-7302-95ac-2130ca65836f	5f00c14f-d22b-43ca-bb62-6ec96c171167	2026-03-29 14:37:37	2026-04-01 14:37:37	2026-03-29 07:37:55.283538
3833f409-227b-4351-9c28-7b5593907de2	naufalardhanijapan@gmail.com	rzRp5CuWvgqGSv0Tb17AOyoylkOBsn13KCfb8T5HgAimAeYv34OyTWFzcHAeSezA	accepted	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	31c7b915-01ea-40ed-80be-723ffe01c10d	2026-04-04 16:45:43	2026-04-07 16:45:43	2026-04-04 09:46:22.40792
\.


--
-- Data for Name: labels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.labels (id, name, color_id, created_at, updated_at, workspace_id) FROM stdin;
9a8252f0-e4d0-4c48-a022-d9e3c8133115	Label Satu	2cf4a4f4-06a0-47c7-876b-2cf788e16351	2026-04-04 12:49:03	2026-04-04 12:49:03	f925311c-f164-4133-8014-2de78bdebaec
\.


--
-- Data for Name: leave_requests; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.leave_requests (id, user_id, workspace_id, leave_type, start_date, end_date, reason, status, approved_by, attachment_url, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.messages (id, conversation_id, sender_id, content, message_type, reply_to_message_id, is_edited, edited_at, deleted_at, created_at, is_read, read_at, updated_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
3	2025_11_27_204411_create_subscription_tables	1
4	2025_11_30_205745_add_system_role_id_to_users_table	2
5	2025_12_20_180641_create_notifications_table	3
6	2025_12_23_110225_add_status_active_to_user_companies_table	4
7	2026_03_16_004805_add_workspace_id_to_labels_table	5
\.


--
-- Data for Name: mindmap_nodes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mindmap_nodes (id, mindmap_id, parent_id, title, description, type, x_position, y_position, connection_side, sort_order, created_at, updated_at) FROM stdin;
54f51246-3bfd-473d-adab-a8ddc58bdcb7	35af81e0-b66e-4a4e-b42c-481b663cd91b	\N	BSS	skdljf	default	276.00	211.00	right:left	0	2026-04-04 11:08:21	2026-04-04 11:08:21
ccfe8faf-b1e2-474d-890c-33303b2ab3b6	35af81e0-b66e-4a4e-b42c-481b663cd91b	\N	Node Baru		default	841.00	147.00	right:left	1	2026-04-04 11:08:21	2026-04-04 11:08:21
\.


--
-- Data for Name: mindmaps; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mindmaps (id, workspace_id, title, description, created_at, updated_at) FROM stdin;
0cf7cfe3-db29-4f95-b163-314beece6d23	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	Mind Map testing	Mind map untuk workspace testing	2026-03-12 13:55:37	2026-03-12 13:55:37
059fde02-bc62-4d7f-b1b8-6724907a1199	325797d8-e3ad-4e66-a280-a8098d195bc8	Mind Map ss	Mind map untuk workspace ss	2026-03-15 22:57:52	2026-03-15 22:57:52
35af81e0-b66e-4a4e-b42c-481b663cd91b	f925311c-f164-4133-8014-2de78bdebaec	Mind Map asdf	Mind map untuk workspace asdf	2026-04-04 11:07:56	2026-04-04 11:07:56
\.


--
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notifications (id, user_id, company_id, workspace_id, type, title, message, context, notifiable_type, notifiable_id, actor_id, is_read, read_at, action_url, created_at, updated_at) FROM stdin;
019d38f7-eaae-702c-be6c-2f0678f4ee01	019d3885-1b56-701e-b2f3-7ca25296665c	31c7b915-01ea-40ed-80be-723ffe01c10d	3b61e2cf-dd4b-4732-ae89-8042450187b2	task	Tugas baru ditugaskan	Eksplorasi Moodboard	Belum ada fase · Div. Desain	App\\Models\\Task	1b6bf068-48ef-40eb-9ba9-441f210d06c8	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/3b61e2cf-dd4b-4732-ae89-8042450187b2?task=1b6bf068-48ef-40eb-9ba9-441f210d06c8	2026-03-29 16:41:04	2026-03-29 16:41:04
019d38f8-7be8-7014-8502-03f8e920a5b1	019d3837-daba-7266-8af4-3cedf35e27da	31c7b915-01ea-40ed-80be-723ffe01c10d	3b61e2cf-dd4b-4732-ae89-8042450187b2	task	Tugas baru ditugaskan	Desain High-Fidelity Dashboard	Belum ada fase · Div. Desain	App\\Models\\Task	92a64a37-173a-4abc-8138-9e3c01843ee8	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/3b61e2cf-dd4b-4732-ae89-8042450187b2?task=92a64a37-173a-4abc-8138-9e3c01843ee8	2026-03-29 16:41:41	2026-03-29 16:41:41
019d38f9-0d0a-7328-ac30-906886257713	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	3b61e2cf-dd4b-4732-ae89-8042450187b2	task	Tugas baru ditugaskan	Revisi Ikon Navigasi V2.1	Belum ada fase · Div. Desain	App\\Models\\Task	e69a60cb-b9d7-45f4-ab54-e8cbdf5735cf	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/3b61e2cf-dd4b-4732-ae89-8042450187b2?task=e69a60cb-b9d7-45f4-ab54-e8cbdf5735cf	2026-03-29 16:42:19	2026-03-29 16:42:19
019d38f9-e901-7104-bbb2-d9be91733506	019d386f-efdf-720e-badc-4570a7ca3df8	31c7b915-01ea-40ed-80be-723ffe01c10d	3b61e2cf-dd4b-4732-ae89-8042450187b2	task	Tugas baru ditugaskan	Finalisasi Desain Poster	Belum ada fase · Div. Desain	App\\Models\\Task	7dcac3f9-2e40-472d-9ad3-94d5578c3d79	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/3b61e2cf-dd4b-4732-ae89-8042450187b2?task=7dcac3f9-2e40-472d-9ad3-94d5578c3d79	2026-03-29 16:43:15	2026-03-29 16:43:15
019d38fa-65fb-733b-9d01-bcb02c8e1535	019d3837-daba-7266-8af4-3cedf35e27da	31c7b915-01ea-40ed-80be-723ffe01c10d	3b61e2cf-dd4b-4732-ae89-8042450187b2	task	Tugas baru ditugaskan	Pembuatan Aset Ilustrasi	Belum ada fase · Div. Desain	App\\Models\\Task	e9b6f2a5-dd40-4860-a99b-262da36c5cbe	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/3b61e2cf-dd4b-4732-ae89-8042450187b2?task=e9b6f2a5-dd40-4860-a99b-262da36c5cbe	2026-03-29 16:43:47	2026-03-29 16:43:47
019d38fa-cd2a-71da-ad7c-052d02f36de7	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	3b61e2cf-dd4b-4732-ae89-8042450187b2	task	Tugas baru ditugaskan	Redesain Komponen Tabel	Belum ada fase · Div. Desain	App\\Models\\Task	367ee4a6-4589-4881-8754-9bff21776e54	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/3b61e2cf-dd4b-4732-ae89-8042450187b2?task=367ee4a6-4589-4881-8754-9bff21776e54	2026-03-29 16:44:13	2026-03-29 16:44:13
019d38fb-3527-7033-8ae4-2f6eed0b9e59	019d386f-efdf-720e-badc-4570a7ca3df8	31c7b915-01ea-40ed-80be-723ffe01c10d	3b61e2cf-dd4b-4732-ae89-8042450187b2	task	Tugas baru ditugaskan	Briefing Desain UI Mobile	Belum ada fase · Div. Desain	App\\Models\\Task	366d5a58-2e0f-48f7-810f-642c48f58f7e	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/3b61e2cf-dd4b-4732-ae89-8042450187b2?task=366d5a58-2e0f-48f7-810f-642c48f58f7e	2026-03-29 16:44:40	2026-03-29 16:44:40
019d38fb-9e66-71f4-b649-da6d829352a0	019d387e-1110-73d8-9259-156362afc755	31c7b915-01ea-40ed-80be-723ffe01c10d	3b61e2cf-dd4b-4732-ae89-8042450187b2	task	Tugas baru ditugaskan	Eksperimen Layout Dark Mode	Belum ada fase · Div. Desain	App\\Models\\Task	359f8ee7-8319-4307-b4cf-3ecaf206f959	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/3b61e2cf-dd4b-4732-ae89-8042450187b2?task=359f8ee7-8319-4307-b4cf-3ecaf206f959	2026-03-29 16:45:07	2026-03-29 16:45:07
019d38ff-16ed-72dd-a9ab-bbf9e97926e2	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	173a617f-c955-42f6-9042-9815a0553ae6	task	Tugas baru ditugaskan	Rekapitulasi Payroll	Belum ada fase · HR Keuangan	App\\Models\\Task	a2701ef2-a3b8-4afa-be22-c1944b89b86a	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/173a617f-c955-42f6-9042-9815a0553ae6?task=a2701ef2-a3b8-4afa-be22-c1944b89b86a	2026-03-29 16:48:54	2026-03-29 16:48:54
019d38ff-927d-71b9-baf4-ef05a12b0fc6	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	173a617f-c955-42f6-9042-9815a0553ae6	task	Tugas baru ditugaskan	Review Anggaran Server	Belum ada fase · HR Keuangan	App\\Models\\Task	e63094f9-baac-4f3a-96b2-2b0224c78f4a	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/173a617f-c955-42f6-9042-9815a0553ae6?task=e63094f9-baac-4f3a-96b2-2b0224c78f4a	2026-03-29 16:49:26	2026-03-29 16:49:26
019d3900-5351-73a3-8f77-efef33337847	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	173a617f-c955-42f6-9042-9815a0553ae6	task	Tugas baru ditugaskan	Pengadaan MacBook Pro	Belum ada fase · HR Keuangan	App\\Models\\Task	eb70b128-eb06-42f5-8780-92451cf06be9	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/173a617f-c955-42f6-9042-9815a0553ae6?task=eb70b128-eb06-42f5-8780-92451cf06be9	2026-03-29 16:50:15	2026-03-29 16:50:15
019d3900-d6be-71d2-9ef1-c817bb297b4e	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	173a617f-c955-42f6-9042-9815a0553ae6	task	Tugas baru ditugaskan	Audit Laporan Pajak	Belum ada fase · HR Keuangan	App\\Models\\Task	ba0c17c5-1bd1-4441-a6da-3de01178ec25	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/173a617f-c955-42f6-9042-9815a0553ae6?task=ba0c17c5-1bd1-4441-a6da-3de01178ec25	2026-03-29 16:50:49	2026-03-29 16:50:49
019d3901-6d48-724f-b4e0-97ae19cf60cf	019d387b-4032-73ae-a0a4-db68da827c4d	31c7b915-01ea-40ed-80be-723ffe01c10d	173a617f-c955-42f6-9042-9815a0553ae6	task	Tugas baru ditugaskan	Screening Kandidat Dev	Belum ada fase · HR Keuangan	App\\Models\\Task	3fa20e1a-c346-4d36-b88d-e2ee90f64802	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/173a617f-c955-42f6-9042-9815a0553ae6?task=3fa20e1a-c346-4d36-b88d-e2ee90f64802	2026-03-29 16:51:28	2026-03-29 16:51:28
019d3901-caa1-7286-af8b-f64e1e537663	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	173a617f-c955-42f6-9042-9815a0553ae6	task	Tugas baru ditugaskan	Pembayaran Invoice Firebase	Belum ada fase · HR Keuangan	App\\Models\\Task	dcd704f9-7638-4247-bbc9-4d0c1a7cb08e	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/173a617f-c955-42f6-9042-9815a0553ae6?task=dcd704f9-7638-4247-bbc9-4d0c1a7cb08e	2026-03-29 16:51:51	2026-03-29 16:51:51
019d3902-137d-714e-ac88-6633a2049bf4	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	173a617f-c955-42f6-9042-9815a0553ae6	task	Tugas baru ditugaskan	Penyusunan Bonus Target	Belum ada fase · HR Keuangan	App\\Models\\Task	baa431ce-a7de-4bb7-bf76-9520b82651e9	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/173a617f-c955-42f6-9042-9815a0553ae6?task=baa431ce-a7de-4bb7-bf76-9520b82651e9	2026-03-29 16:52:10	2026-03-29 16:52:10
019d3904-49bd-739f-8f92-db607b4d182d	019d3837-daba-7266-8af4-3cedf35e27da	31c7b915-01ea-40ed-80be-723ffe01c10d	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	task	Tugas baru ditugaskan	Penandatanganan Kontrak	Belum ada fase · MOU PT Xyz	App\\Models\\Task	cbdb59b1-1390-4b67-aa0c-12a0dc92d7cd	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/8e26fb16-12bc-4768-b8b6-72f7d28efcc8?task=cbdb59b1-1390-4b67-aa0c-12a0dc92d7cd	2026-03-29 16:54:35	2026-03-29 16:54:35
019d3904-ca50-709b-83cf-69d80966f6d5	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	task	Tugas baru ditugaskan	Kick-off Meeting Stakeholder	Belum ada fase · MOU PT Xyz	App\\Models\\Task	3c14b0ad-f79b-4bff-86a8-bd6f24d3fe1f	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/8e26fb16-12bc-4768-b8b6-72f7d28efcc8?task=3c14b0ad-f79b-4bff-86a8-bd6f24d3fe1f	2026-03-29 16:55:08	2026-03-29 16:55:08
019d3905-2ef5-718a-b002-735912899842	019d387e-1110-73d8-9259-156362afc755	31c7b915-01ea-40ed-80be-723ffe01c10d	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	task	Tugas baru ditugaskan	Setup Lingkungan Staging	Belum ada fase · MOU PT Xyz	App\\Models\\Task	d2d4b1f6-8c11-4efe-8ba9-7aecf7cea4f8	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/8e26fb16-12bc-4768-b8b6-72f7d28efcc8?task=d2d4b1f6-8c11-4efe-8ba9-7aecf7cea4f8	2026-03-29 16:55:34	2026-03-29 16:55:34
019d3907-e025-72f3-8946-e37f627887d0	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	173a617f-c955-42f6-9042-9815a0553ae6	task	Tugas diperbarui	Pengadaan MacBook Pro telah diperbarui	Belum ada fase · HR Keuangan	App\\Models\\Task	eb70b128-eb06-42f5-8780-92451cf06be9	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/kanban-tugas/173a617f-c955-42f6-9042-9815a0553ae6?task=eb70b128-eb06-42f5-8780-92451cf06be9	2026-03-29 16:58:30	2026-03-29 16:58:30
019d4d6e-58a8-702a-969e-47f3cefa6333	019d3885-1b56-701e-b2f3-7ca25296665c	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	f014093e-93b3-413e-9655-0b5b1b32b1cf	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/f014093e-93b3-413e-9655-0b5b1b32b1cf	2026-04-02 16:02:50	2026-04-02 16:02:50
019d4d6e-59bd-716c-a183-0285c079519a	019d3837-daba-7266-8af4-3cedf35e27da	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	f014093e-93b3-413e-9655-0b5b1b32b1cf	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/f014093e-93b3-413e-9655-0b5b1b32b1cf	2026-04-02 16:02:50	2026-04-02 16:02:50
019d4d6e-59cd-70a5-a5cf-df7deda46d93	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	f014093e-93b3-413e-9655-0b5b1b32b1cf	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/f014093e-93b3-413e-9655-0b5b1b32b1cf	2026-04-02 16:02:50	2026-04-02 16:02:50
019d4d6e-59dc-707e-8758-91dfc5820e22	019d386f-efdf-720e-badc-4570a7ca3df8	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	f014093e-93b3-413e-9655-0b5b1b32b1cf	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/f014093e-93b3-413e-9655-0b5b1b32b1cf	2026-04-02 16:02:50	2026-04-02 16:02:50
019d4d6e-59ff-7381-a4cc-a277593e0b26	019d387b-4032-73ae-a0a4-db68da827c4d	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	f014093e-93b3-413e-9655-0b5b1b32b1cf	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/f014093e-93b3-413e-9655-0b5b1b32b1cf	2026-04-02 16:02:50	2026-04-02 16:02:50
019d4d6e-5a10-7350-bd44-4e23b950434f	019d387e-1110-73d8-9259-156362afc755	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	f014093e-93b3-413e-9655-0b5b1b32b1cf	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/f014093e-93b3-413e-9655-0b5b1b32b1cf	2026-04-02 16:02:50	2026-04-02 16:02:50
019d4d71-02ca-710c-a0eb-998aee4531e1	019d3885-1b56-701e-b2f3-7ca25296665c	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	a63b041a-ccce-4737-8089-e8ba8adffe79	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/a63b041a-ccce-4737-8089-e8ba8adffe79	2026-04-02 16:05:45	2026-04-02 16:05:45
019d4d71-067b-7284-bbb8-22853b0cdada	019d3837-daba-7266-8af4-3cedf35e27da	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	a63b041a-ccce-4737-8089-e8ba8adffe79	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/a63b041a-ccce-4737-8089-e8ba8adffe79	2026-04-02 16:05:46	2026-04-02 16:05:46
019d4d71-0694-73bf-9a7e-0576c5161f23	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	a63b041a-ccce-4737-8089-e8ba8adffe79	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/a63b041a-ccce-4737-8089-e8ba8adffe79	2026-04-02 16:05:46	2026-04-02 16:05:46
019d4d71-06a6-70e9-bf90-1e4a51952684	019d386f-efdf-720e-badc-4570a7ca3df8	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	a63b041a-ccce-4737-8089-e8ba8adffe79	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/a63b041a-ccce-4737-8089-e8ba8adffe79	2026-04-02 16:05:46	2026-04-02 16:05:46
019d4d71-06c2-7021-b719-b4f6ee603c45	019d387b-4032-73ae-a0a4-db68da827c4d	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	a63b041a-ccce-4737-8089-e8ba8adffe79	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/a63b041a-ccce-4737-8089-e8ba8adffe79	2026-04-02 16:05:46	2026-04-02 16:05:46
019d4d71-06d6-734e-a014-0cc5aa012583	019d387e-1110-73d8-9259-156362afc755	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman gajian	Pengumuman Perusahaan	App\\Models\\Pengumuman	a63b041a-ccce-4737-8089-e8ba8adffe79	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/a63b041a-ccce-4737-8089-e8ba8adffe79	2026-04-02 16:05:46	2026-04-02 16:05:46
019d4d74-1dba-72b3-bea5-4ded39cf4ae0	019d3885-1b56-701e-b2f3-7ca25296665c	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	prubaha shif	Pengumuman Perusahaan	App\\Models\\Pengumuman	d6ac3bdc-7dec-4caa-882b-8dd1352774e9	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/d6ac3bdc-7dec-4caa-882b-8dd1352774e9	2026-04-02 16:09:08	2026-04-02 16:09:08
019d4d74-2212-7392-b9e1-03cbcbb9a5e6	019d3837-daba-7266-8af4-3cedf35e27da	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	prubaha shif	Pengumuman Perusahaan	App\\Models\\Pengumuman	d6ac3bdc-7dec-4caa-882b-8dd1352774e9	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/d6ac3bdc-7dec-4caa-882b-8dd1352774e9	2026-04-02 16:09:09	2026-04-02 16:09:09
019d4d74-229d-7059-a07a-afe4c2b968ac	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	prubaha shif	Pengumuman Perusahaan	App\\Models\\Pengumuman	d6ac3bdc-7dec-4caa-882b-8dd1352774e9	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/d6ac3bdc-7dec-4caa-882b-8dd1352774e9	2026-04-02 16:09:09	2026-04-02 16:09:09
019d4d74-22aa-7064-988e-060ae3c384af	019d386f-efdf-720e-badc-4570a7ca3df8	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	prubaha shif	Pengumuman Perusahaan	App\\Models\\Pengumuman	d6ac3bdc-7dec-4caa-882b-8dd1352774e9	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/d6ac3bdc-7dec-4caa-882b-8dd1352774e9	2026-04-02 16:09:09	2026-04-02 16:09:09
019d4d74-22c4-7251-88d8-112616a7f075	019d387b-4032-73ae-a0a4-db68da827c4d	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	prubaha shif	Pengumuman Perusahaan	App\\Models\\Pengumuman	d6ac3bdc-7dec-4caa-882b-8dd1352774e9	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/d6ac3bdc-7dec-4caa-882b-8dd1352774e9	2026-04-02 16:09:09	2026-04-02 16:09:09
019d4d74-234c-70a6-97eb-0517b7af1ad6	019d387e-1110-73d8-9259-156362afc755	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	prubaha shif	Pengumuman Perusahaan	App\\Models\\Pengumuman	d6ac3bdc-7dec-4caa-882b-8dd1352774e9	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/d6ac3bdc-7dec-4caa-882b-8dd1352774e9	2026-04-02 16:09:10	2026-04-02 16:09:10
019d4d99-9edd-7226-9c58-6b7e6409b2b2	019d3885-1b56-701e-b2f3-7ca25296665c	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman anggota baru	Pengumuman Perusahaan	App\\Models\\Pengumuman	6ce8e396-7497-498d-bb0a-a607d337fffd	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/6ce8e396-7497-498d-bb0a-a607d337fffd	2026-04-02 16:50:06	2026-04-02 16:50:06
019d4d99-a226-700e-a49e-3e4287ce2121	019d3837-daba-7266-8af4-3cedf35e27da	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman anggota baru	Pengumuman Perusahaan	App\\Models\\Pengumuman	6ce8e396-7497-498d-bb0a-a607d337fffd	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/6ce8e396-7497-498d-bb0a-a607d337fffd	2026-04-02 16:50:07	2026-04-02 16:50:07
019d4d99-a2f1-70e1-9dc8-11fe0ae7b2e0	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman anggota baru	Pengumuman Perusahaan	App\\Models\\Pengumuman	6ce8e396-7497-498d-bb0a-a607d337fffd	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/6ce8e396-7497-498d-bb0a-a607d337fffd	2026-04-02 16:50:07	2026-04-02 16:50:07
019d4d99-a31a-72af-b817-0bc81182c7a5	019d386f-efdf-720e-badc-4570a7ca3df8	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman anggota baru	Pengumuman Perusahaan	App\\Models\\Pengumuman	6ce8e396-7497-498d-bb0a-a607d337fffd	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/6ce8e396-7497-498d-bb0a-a607d337fffd	2026-04-02 16:50:07	2026-04-02 16:50:07
019d4d99-a33a-7169-94f6-ffd6f86d0806	019d387b-4032-73ae-a0a4-db68da827c4d	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman anggota baru	Pengumuman Perusahaan	App\\Models\\Pengumuman	6ce8e396-7497-498d-bb0a-a607d337fffd	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/6ce8e396-7497-498d-bb0a-a607d337fffd	2026-04-02 16:50:07	2026-04-02 16:50:07
019d4d99-a364-73c4-9b8b-e906db7888f2	019d387e-1110-73d8-9259-156362afc755	31c7b915-01ea-40ed-80be-723ffe01c10d	\N	announcement	Pengumuman baru	pengumuman anggota baru	Pengumuman Perusahaan	App\\Models\\Pengumuman	6ce8e396-7497-498d-bb0a-a607d337fffd	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	f	\N	http://localhost:8000/companies/31c7b915-01ea-40ed-80be-723ffe01c10d/pengumuman-perusahaan/6ce8e396-7497-498d-bb0a-a607d337fffd	2026-04-02 16:50:07	2026-04-02 16:50:07
\.


--
-- Data for Name: otp_verifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.otp_verifications (id, email, otp, type, expires_at, is_used, created_at, updated_at) FROM stdin;
1	naufal201080@gmail.com	829412	register	2026-03-29 12:25:57	t	2026-03-29 12:15:57	2026-03-29 12:16:16
2	kukvu39fzu@lnovic.com	575600	register	2026-03-29 13:20:57	t	2026-03-29 13:10:57	2026-03-29 13:11:17
3	wixaya4731@jsncos.com	541999	register	2026-03-29 13:24:13	t	2026-03-29 13:14:13	2026-03-29 13:15:03
5	medeso9204@smkanba.com	240815	register	2026-03-29 13:27:37	t	2026-03-29 13:17:37	2026-03-29 13:18:15
6	lefoxik200@smkanba.com	203793	register	2026-03-29 14:12:45	t	2026-03-29 14:02:45	2026-03-29 14:03:22
7	hexaxel671@izkat.com	211766	register	2026-03-29 14:18:34	t	2026-03-29 14:08:34	2026-03-29 14:09:11
8	cedaf73762@muncloud.com	552175	register	2026-03-29 14:21:59	t	2026-03-29 14:11:59	2026-03-29 14:12:33
9	krii5v4jvq@ozsaip.com	496661	register	2026-03-29 14:34:34	t	2026-03-29 14:24:34	2026-03-29 14:24:54
10	9uln0hwmrm@bwmyga.com	111103	register	2026-03-29 14:36:43	t	2026-03-29 14:26:43	2026-03-29 14:27:59
11	8lka2omkeo@wnbaldwy.com	311866	register	2026-03-29 14:41:18	t	2026-03-29 14:31:18	2026-03-29 14:31:30
12	q0qy6ex4on@bwmyga.com	099821	register	2026-03-29 14:45:24	t	2026-03-29 14:35:24	2026-03-29 14:35:40
13	tester@tester.com	358844	register	2026-04-03 04:04:00	f	2026-04-03 03:54:00	2026-04-03 03:54:00
\.


--
-- Data for Name: plans; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.plans (id, plan_name, price_monthly, base_user_limit, description, is_active, created_at, updated_at) FROM stdin;
e0a92975-a9b3-4542-9930-83d756e01c04	Paket Basic	15000.00	5	Cocok untuk tim kecil	t	2025-12-28 13:16:13	2025-12-28 13:16:13
a2af066c-3bcc-4f67-a005-4cae640ea8b1	Paket Standard	45000.00	20	Untuk tim yang berkembang	t	2025-12-28 13:16:13	2025-12-28 13:16:13
044b0518-b4e8-4cef-9892-1f86311ae0f3	Paket Business	100000.00	50	Untuk organisasi besar	t	2025-12-28 13:16:13	2025-12-28 13:16:13
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name) FROM stdin;
11111111-1111-1111-1111-111111111111	SuperAdmin
a688ef38-3030-45cb-9a4d-0407605bc322	Manager
33333333-3333-3333-3333-333333333333	AdminSistem
ed81bd39-9041-43b8-a504-bf743b5c2919	Member
55555555-5555-5555-5555-555555555555	Administrator
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
UskDj4LiH4ma6hnFB5H0EwxIr7rEqrAAhxQ8yxaQ	\N	192.168.65.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0	YTo0OntzOjY6Il90b2tlbiI7czo0MDoia3h0ckZFS0NTWVZSbUh2dkdGSnFrR3V4dHlmdk5URHlFQjZLdmpibSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvbWFzdWsiO3M6NToicm91dGUiO3M6NToibWFzdWsiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1775295877
4B9JZb4gnl1BdmintvZEdigFTwGaskUwnT43s5Lk	\N	192.168.65.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0	YTozOntzOjY6Il90b2tlbiI7czo0MDoiQnlaZEtoMDkzZWlmc0RKTFlMU0RLSjllUDBRdFg1ZDlsSGN1NTVmaiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9tYXN1ayI7czo1OiJyb3V0ZSI7czo1OiJtYXN1ayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1775295876
jIEIddZFhBbE4QedRyyNTij6t4SAn34jzL5GGfkv	\N	192.168.65.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0	YTozOntzOjY6Il90b2tlbiI7czo0MDoiMThnSjl2ZXJIRlVLVHpsUnZKYTk0c2VzYm9QYkw3YXRWajNDQ0NnRyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9tYXN1ayI7czo1OiJyb3V0ZSI7czo1OiJtYXN1ayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1775295878
dqVwq6LWgzIjZvvzboInth041Rcy7R8IcySrw4eo	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	192.168.65.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36	YTo2OntzOjY6Il90b2tlbiI7czo0MDoibXcwRHFnYTRsZW9zQkxHaTZPMllrUUs5Um1HNWdsQXJZZXlnZnZ0SyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtzOjM2OiIwMTljYzliYi1mNmZkLTcyOGUtODBiMi04YmFmNTdhYzZiNmUiO3M6MTc6ImFjdGl2ZV9jb21wYW55X2lkIjtzOjM2OiIzMWM3YjkxNS0wMWVhLTQwZWQtODBiZS03MjNmZmUwMWMxMGQiO30=	1775296272
NpP8ZGEIJHkOsxDA0QoJrsAMWEAw57dgCoVup4gP	019ce093-8522-725c-8f9c-9b3928ec6ad3	192.168.65.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.2 Safari/605.1.15	YTo2OntzOjY6Il90b2tlbiI7czo0MDoiZjhUV3M1aW9qWGc1VTNPZDgzZ2FtODNZdVVNSTdSd2JhMXgyVXB3UyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI5OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvcHJvZmlsZSI7czo1OiJyb3V0ZSI7czoxMzoicHJvZmlsZS5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtzOjM2OiIwMTljZTA5My04NTIyLTcyNWMtOGY5Yy05YjM5MjhlYzZhZDMiO3M6MTc6ImFjdGl2ZV9jb21wYW55X2lkIjtzOjM2OiIzMWM3YjkxNS0wMWVhLTQwZWQtODBiZS03MjNmZmUwMWMxMGQiO30=	1775296292
\.


--
-- Data for Name: subscription_invoices; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.subscription_invoices (id, subscription_id, external_id, payment_url, amount, billing_month, status, paid_at, payment_details, created_at, updated_at, payment_method, proof_of_payment, admin_notes, verified_at, verified_by, payer_name, payer_bank, payer_account_number) FROM stdin;
019d5712-479b-7052-a8d9-025fc07b8e53	019d56e7-34da-7395-af05-b2ef45b6053e	INV-1775282309-392	https://checkout.xendit.co/web/69d0a88562b36e8fa05bb710	100000.00	2026-04	pending	\N	{"plan_id":"044b0518-b4e8-4cef-9892-1f86311ae0f3","plan_name":"Paket Business","new_addon_count":0,"new_total_limit":50,"is_downgrade":false}	2026-04-04 12:58:29	2026-04-04 12:58:29	xendit	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: subscriptions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.subscriptions (id, company_id, plan_id, addons_user_count, total_user_limit, start_date, end_date, status, created_at, updated_at, deleted_at) FROM stdin;
019d56e7-34da-7395-af05-b2ef45b6053e	aed661f0-a039-4927-8259-6ea71f9943f7	\N	0	0	2026-04-04 12:11:26	2026-04-11 12:11:26	trial	2026-04-04 12:11:26	2026-04-04 12:11:26	\N
\.


--
-- Data for Name: task_assignments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.task_assignments (id, task_id, user_id, assigned_at) FROM stdin;
31845897-0dab-4786-adcd-8849970bcd48	1b6bf068-48ef-40eb-9ba9-441f210d06c8	019d3885-1b56-701e-b2f3-7ca25296665c	2026-03-29 16:41:04
0dff1fe4-7226-40eb-b35d-c9e2356c6dc0	92a64a37-173a-4abc-8138-9e3c01843ee8	019d3837-daba-7266-8af4-3cedf35e27da	2026-03-29 16:41:41
1a4786d3-6185-40cf-a9ce-318d7608ddc9	e69a60cb-b9d7-45f4-ab54-e8cbdf5735cf	019d3867-8844-734b-90a1-dc1d25affd5c	2026-03-29 16:42:19
f2801b7c-ffdd-4562-946a-4b42e8bcc69f	7dcac3f9-2e40-472d-9ad3-94d5578c3d79	019d386f-efdf-720e-badc-4570a7ca3df8	2026-03-29 16:43:15
c73584d0-21ce-4214-8955-66e28cdf3d2d	e9b6f2a5-dd40-4860-a99b-262da36c5cbe	019d3837-daba-7266-8af4-3cedf35e27da	2026-03-29 16:43:47
369af238-a292-4cf2-98d0-0a61bb6f5f1d	367ee4a6-4589-4881-8754-9bff21776e54	019d3867-8844-734b-90a1-dc1d25affd5c	2026-03-29 16:44:13
5b5463f2-bbf0-42b3-b06a-d8774c3b9cea	366d5a58-2e0f-48f7-810f-642c48f58f7e	019d386f-efdf-720e-badc-4570a7ca3df8	2026-03-29 16:44:40
95ec300d-8985-4dab-a2f6-cf877b1f3253	359f8ee7-8319-4307-b4cf-3ecaf206f959	019d387e-1110-73d8-9259-156362afc755	2026-03-29 16:45:07
eb0c23a4-8c6a-4227-9a56-310b6c612f5a	a2701ef2-a3b8-4afa-be22-c1944b89b86a	019d3867-8844-734b-90a1-dc1d25affd5c	2026-03-29 16:48:54
a70732b0-380d-42c5-af62-662c28b8a0d3	e63094f9-baac-4f3a-96b2-2b0224c78f4a	019d3867-8844-734b-90a1-dc1d25affd5c	2026-03-29 16:49:26
a06ae1b8-b55e-425c-9cd8-0645521761c5	ba0c17c5-1bd1-4441-a6da-3de01178ec25	019d3867-8844-734b-90a1-dc1d25affd5c	2026-03-29 16:50:49
1d2ab4fa-4bac-4bd5-9ce3-d25dbc32497e	3fa20e1a-c346-4d36-b88d-e2ee90f64802	019d387b-4032-73ae-a0a4-db68da827c4d	2026-03-29 16:51:28
84983f10-6af6-4253-ab2e-7e5df6d0d675	dcd704f9-7638-4247-bbc9-4d0c1a7cb08e	019d3867-8844-734b-90a1-dc1d25affd5c	2026-03-29 16:51:51
ad7de793-0d06-48c5-8db0-99e4e11fbbd8	baa431ce-a7de-4bb7-bf76-9520b82651e9	019d3867-8844-734b-90a1-dc1d25affd5c	2026-03-29 16:52:10
34065269-caef-4593-b5b4-32344a6b918e	cbdb59b1-1390-4b67-aa0c-12a0dc92d7cd	019d3837-daba-7266-8af4-3cedf35e27da	2026-03-29 16:54:35
e92852ad-4e8f-40cf-8142-7b464045f4ee	3c14b0ad-f79b-4bff-86a8-bd6f24d3fe1f	019d3867-8844-734b-90a1-dc1d25affd5c	2026-03-29 16:55:08
2fe30c74-afd7-4b07-ac9f-d3c59a657bbd	d2d4b1f6-8c11-4efe-8ba9-7aecf7cea4f8	019d387e-1110-73d8-9259-156362afc755	2026-03-29 16:55:34
c3e14087-0736-485f-9693-b18b6fb0e7f1	eb70b128-eb06-42f5-8780-92451cf06be9	019d3867-8844-734b-90a1-dc1d25affd5c	2026-03-29 16:58:30
\.


--
-- Data for Name: task_labels; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.task_labels (task_id, label_id, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: tasks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tasks (id, workspace_id, created_by, title, description, status, board_column_id, priority, is_secret, start_datetime, due_datetime, created_at, updated_at, deleted_at, phase, completed_at) FROM stdin;
369ec020-dad9-466c-a275-f1ece366c7bc	325797d8-e3ad-4e66-a280-a8098d195bc8	019ce093-8522-725c-8f9c-9b3928ec6ad3	lkkl	<p>asdf</p>	inprogress	873269f3-9043-4fad-a924-eaea0bc6be8f	medium	f	2026-03-15 22:59:00	2026-04-03 22:59:00	2026-03-15 22:59:28	2026-03-15 22:59:31	\N	lkjl	\N
1b6bf068-48ef-40eb-9ba9-441f210d06c8	3b61e2cf-dd4b-4732-ae89-8042450187b2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Eksplorasi Moodboard	<p>kerjakan</p>	done	287fecc1-663a-49da-8a79-726137aa4831	medium	f	2026-03-29 16:40:00	2026-03-31 16:41:00	2026-03-29 16:41:04	2026-03-29 16:41:04	\N	\N	\N
92a64a37-173a-4abc-8138-9e3c01843ee8	3b61e2cf-dd4b-4732-ae89-8042450187b2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Desain High-Fidelity Dashboard	<p>kerjakan</p>	inprogress	5910b39d-5ad7-4be9-8b61-26c907c8e1b1	medium	f	2026-03-29 16:41:00	2026-04-02 16:41:00	2026-03-29 16:41:41	2026-03-29 16:41:41	\N	\N	\N
e69a60cb-b9d7-45f4-ab54-e8cbdf5735cf	3b61e2cf-dd4b-4732-ae89-8042450187b2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Revisi Ikon Navigasi V2.1	<p>kerjakan</p>	todo	8cf170fb-2a0a-4ea3-8931-af4d71684b8a	medium	f	2026-03-29 16:42:00	2026-04-11 16:42:00	2026-03-29 16:42:19	2026-03-29 16:42:19	\N	\N	\N
7dcac3f9-2e40-472d-9ad3-94d5578c3d79	3b61e2cf-dd4b-4732-ae89-8042450187b2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Finalisasi Desain Poster	<p>kerjakan</p>	done	287fecc1-663a-49da-8a79-726137aa4831	medium	f	2026-03-29 16:43:00	2026-04-11 16:43:00	2026-03-29 16:43:15	2026-03-29 16:43:15	\N	\N	\N
e9b6f2a5-dd40-4860-a99b-262da36c5cbe	3b61e2cf-dd4b-4732-ae89-8042450187b2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Pembuatan Aset Ilustrasi	<p>kerjakan</p>	inprogress	5910b39d-5ad7-4be9-8b61-26c907c8e1b1	medium	f	2026-03-29 16:43:00	2026-04-08 16:43:00	2026-03-29 16:43:47	2026-03-29 16:43:47	\N	\N	\N
367ee4a6-4589-4881-8754-9bff21776e54	3b61e2cf-dd4b-4732-ae89-8042450187b2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Redesain Komponen Tabel	<p>kk</p>	todo	8cf170fb-2a0a-4ea3-8931-af4d71684b8a	medium	f	2026-03-29 16:44:00	2026-04-10 16:44:00	2026-03-29 16:44:13	2026-03-29 16:44:13	\N	\N	\N
366d5a58-2e0f-48f7-810f-642c48f58f7e	3b61e2cf-dd4b-4732-ae89-8042450187b2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Briefing Desain UI Mobile	<p>kjlkj</p>	todo	8cf170fb-2a0a-4ea3-8931-af4d71684b8a	medium	f	2026-03-29 16:44:00	2026-04-08 16:44:00	2026-03-29 16:44:40	2026-03-29 16:44:40	\N	\N	\N
359f8ee7-8319-4307-b4cf-3ecaf206f959	3b61e2cf-dd4b-4732-ae89-8042450187b2	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Eksperimen Layout Dark Mode	<p>knlkjkl</p>	cancel	1b7785ee-06e4-4d9c-a7b4-1a627d105136	medium	f	2026-03-29 16:45:00	2026-04-01 16:45:00	2026-03-29 16:45:07	2026-03-29 16:45:07	\N	\N	\N
a2701ef2-a3b8-4afa-be22-c1944b89b86a	173a617f-c955-42f6-9042-9815a0553ae6	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Rekapitulasi Payroll	<p>asd</p>	done	ca50d7e9-6f3a-4429-8798-974617a7c5c7	medium	f	2026-03-29 16:47:00	2026-03-29 21:48:00	2026-03-29 16:48:54	2026-03-29 16:48:54	\N	\N	\N
cbdb59b1-1390-4b67-aa0c-12a0dc92d7cd	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Penandatanganan Kontrak	<p>a</p>	done	5d67cc85-b038-4ab4-a819-8bd17902ac21	medium	f	2026-03-29 16:54:00	2026-03-31 16:54:00	2026-03-29 16:54:35	2026-03-29 16:54:35	\N	\N	\N
3c14b0ad-f79b-4bff-86a8-bd6f24d3fe1f	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Kick-off Meeting Stakeholder	<p>sf</p>	done	5d67cc85-b038-4ab4-a819-8bd17902ac21	medium	f	2026-03-29 16:55:00	2026-03-31 16:55:00	2026-03-29 16:55:08	2026-03-29 16:55:08	\N	\N	\N
d2d4b1f6-8c11-4efe-8ba9-7aecf7cea4f8	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Setup Lingkungan Staging	<p>a</p>	done	5d67cc85-b038-4ab4-a819-8bd17902ac21	medium	f	2026-03-29 16:55:00	2026-03-31 16:55:00	2026-03-29 16:55:34	2026-03-29 16:56:01	\N	\N	2026-03-29 16:56:01+00
e63094f9-baac-4f3a-96b2-2b0224c78f4a	173a617f-c955-42f6-9042-9815a0553ae6	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Review Anggaran Server	<p>asfa</p>	done	ca50d7e9-6f3a-4429-8798-974617a7c5c7	medium	f	2026-03-29 16:49:00	2026-03-29 23:59:00	2026-03-29 16:49:26	2026-03-29 16:56:48	\N	\N	2026-03-29 16:56:48+00
ba0c17c5-1bd1-4441-a6da-3de01178ec25	173a617f-c955-42f6-9042-9815a0553ae6	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Audit Laporan Pajak	<p>kk</p>	done	ca50d7e9-6f3a-4429-8798-974617a7c5c7	medium	f	2026-03-29 16:50:00	2026-03-30 16:50:00	2026-03-29 16:50:49	2026-03-29 16:56:49	\N	\N	2026-03-29 16:56:49+00
baa431ce-a7de-4bb7-bf76-9520b82651e9	173a617f-c955-42f6-9042-9815a0553ae6	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Penyusunan Bonus Target	<p>ss</p>	done	ca50d7e9-6f3a-4429-8798-974617a7c5c7	medium	f	2026-03-29 16:52:00	2026-04-01 16:52:00	2026-03-29 16:52:10	2026-03-29 16:56:50	\N	\N	2026-03-29 16:56:50+00
dcd704f9-7638-4247-bbc9-4d0c1a7cb08e	173a617f-c955-42f6-9042-9815a0553ae6	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Pembayaran Invoice Firebase	<p>a</p>	done	ca50d7e9-6f3a-4429-8798-974617a7c5c7	medium	f	2026-03-29 16:51:00	2026-03-31 16:51:00	2026-03-29 16:51:51	2026-03-29 16:56:52	\N	\N	2026-03-29 16:56:52+00
3fa20e1a-c346-4d36-b88d-e2ee90f64802	173a617f-c955-42f6-9042-9815a0553ae6	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Screening Kandidat Dev	<p>fdadsf</p>	done	ca50d7e9-6f3a-4429-8798-974617a7c5c7	medium	f	2026-03-29 16:51:00	2026-03-31 16:51:00	2026-03-29 16:51:28	2026-03-29 16:56:53	\N	\N	2026-03-29 16:56:53+00
eb70b128-eb06-42f5-8780-92451cf06be9	173a617f-c955-42f6-9042-9815a0553ae6	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Pengadaan MacBook Pro	<p>sfdasd</p>	done	ca50d7e9-6f3a-4429-8798-974617a7c5c7	medium	f	2026-03-24 16:49:00	2026-03-29 23:49:00	2026-03-29 16:50:15	2026-03-29 16:58:30	\N	\N	2026-03-29 16:56:46+00
9c686a52-d4a2-4715-9ee0-517276460e47	a9a33d65-19f4-4aed-b677-934bb14721e4	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	sasuke	<p>askdfjakl</p>	todo	53b75417-68dd-4155-af73-bbe0d36fbac8	medium	f	2026-04-04 12:49:00	2026-04-04 16:49:00	2026-04-04 12:49:58	2026-04-04 12:49:58	\N	desain	\N
\.


--
-- Data for Name: user_companies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_companies (id, user_id, company_id, roles_id, created_at, updated_at, deleted_at, status_active) FROM stdin;
64319d9f-611d-4329-8ebc-f6a512878b42	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	a55a03f3-2191-4b53-833c-d7de8ce62c9b	11111111-1111-1111-1111-111111111111	2025-12-28 13:16:28	2025-12-28 13:16:28	\N	t
4aff9d33-7e3c-40fe-8d78-8546a11be9ec	019ce093-8522-725c-8f9c-9b3928ec6ad3	da242fb4-a281-4339-a8a4-9a66436e237d	11111111-1111-1111-1111-111111111111	2026-03-12 12:46:31	2026-03-12 12:46:31	\N	t
9560ffed-5a6d-48c0-8a8d-fceb63eae7ae	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	aed661f0-a039-4927-8259-6ea71f9943f7	11111111-1111-1111-1111-111111111111	2026-03-28 15:16:56	2026-03-28 15:16:56	\N	t
72a84e73-ade6-497d-b971-ffcca256d2b3	019d380f-7435-7156-b5f5-3a997c9f8879	ce50b57e-01c9-4cbf-a4d0-dd57c81939ad	11111111-1111-1111-1111-111111111111	2026-03-29 12:27:25	2026-03-29 12:27:25	\N	t
a60a5c50-db09-4a2a-84ee-13942875d211	019d3837-daba-7266-8af4-3cedf35e27da	aed661f0-a039-4927-8259-6ea71f9943f7	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 13:11:32	2026-03-29 13:11:32	\N	t
6a5a0f8c-0eb2-4b26-8d16-251bd53cbf4a	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	31c7b915-01ea-40ed-80be-723ffe01c10d	11111111-1111-1111-1111-111111111111	2026-03-29 13:24:43	2026-03-29 13:24:43	\N	t
0b248644-563a-44b2-abdd-6813f394b829	019d3837-daba-7266-8af4-3cedf35e27da	31c7b915-01ea-40ed-80be-723ffe01c10d	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 13:25:09	2026-03-29 13:25:09	\N	t
6d4c07d6-1309-4edf-9d61-9716bf530059	019d3867-8844-734b-90a1-dc1d25affd5c	31c7b915-01ea-40ed-80be-723ffe01c10d	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 14:05:52	2026-03-29 14:05:52	\N	t
d20ae915-57eb-49cf-8730-70907d6639f7	019d386f-efdf-720e-badc-4570a7ca3df8	31c7b915-01ea-40ed-80be-723ffe01c10d	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 14:12:40	2026-03-29 14:12:40	\N	t
6882af03-7e08-4fb7-80f8-5d39652f3fc3	019d387b-4032-73ae-a0a4-db68da827c4d	31c7b915-01ea-40ed-80be-723ffe01c10d	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 14:25:17	2026-03-29 14:25:17	\N	t
ea7e0ca1-234f-4623-9073-9e530c485372	019d387e-1110-73d8-9259-156362afc755	31c7b915-01ea-40ed-80be-723ffe01c10d	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 14:28:08	2026-03-29 14:28:08	\N	t
8514a593-adcb-4d23-842f-f41b95cd2bfa	019d3881-49e4-7302-95ac-2130ca65836f	5f00c14f-d22b-43ca-bb62-6ec96c171167	11111111-1111-1111-1111-111111111111	2026-03-29 14:31:43	2026-03-29 14:31:43	\N	t
b7ba67f0-f389-4fee-baa7-1f109b5ad17f	019d3885-1b56-701e-b2f3-7ca25296665c	31c7b915-01ea-40ed-80be-723ffe01c10d	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 14:35:48	2026-03-29 14:35:48	\N	t
feccfd2e-728d-438c-ac79-5865cee242d6	019d3886-3978-7268-98fb-fe96149f4393	f4c42d62-601d-4061-8553-70a6c7171a77	11111111-1111-1111-1111-111111111111	2026-03-29 14:37:05	2026-03-29 14:37:05	\N	t
ba317fca-7b65-44d2-bb4c-c3a2cb9154ec	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	5f00c14f-d22b-43ca-bb62-6ec96c171167	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 14:37:55	2026-03-29 14:37:55	\N	t
fcc01c4a-1a86-465c-a0b4-0f50b63a9e70	019d47ce-c77e-70d0-847c-da03702ad488	705411fd-87ba-4a7f-8e4c-e5034b420ed4	11111111-1111-1111-1111-111111111111	2026-04-01 13:50:56	2026-04-01 13:50:56	\N	t
5bc23b7d-fa2a-4938-ba18-0b28079e9138	019ce093-8522-725c-8f9c-9b3928ec6ad3	31c7b915-01ea-40ed-80be-723ffe01c10d	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-04-04 16:46:22	2026-04-04 16:46:22	\N	t
\.


--
-- Data for Name: user_workspaces; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_workspaces (id, user_id, workspace_id, roles_id, join_date, status_active, updated_at, created_at) FROM stdin;
bdbbe749-7454-42fd-bd4c-2e01f8fc519a	019d3885-1b56-701e-b2f3-7ca25296665c	3b61e2cf-dd4b-4732-ae89-8042450187b2	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 15:12:48	t	2026-03-29 15:12:48	2026-03-29 15:12:48
890e2618-3910-4f57-af39-34d3f6b5fde8	019d3837-daba-7266-8af4-3cedf35e27da	3b61e2cf-dd4b-4732-ae89-8042450187b2	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 15:12:48	t	2026-03-29 15:12:48	2026-03-29 15:12:48
7557c21d-d952-47ed-95e6-d521819a3c8a	019d3867-8844-734b-90a1-dc1d25affd5c	3b61e2cf-dd4b-4732-ae89-8042450187b2	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 15:12:48	t	2026-03-29 15:12:48	2026-03-29 15:12:48
f5a4688e-4222-450a-a8c4-ec95aae8ea8e	019d386f-efdf-720e-badc-4570a7ca3df8	3b61e2cf-dd4b-4732-ae89-8042450187b2	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 15:12:48	t	2026-03-29 15:12:48	2026-03-29 15:12:48
e8d55b09-09f3-4832-a695-5415b96dccc7	019d387e-1110-73d8-9259-156362afc755	3b61e2cf-dd4b-4732-ae89-8042450187b2	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 15:12:48	t	2026-03-29 15:12:48	2026-03-29 15:12:48
3187af8e-84d4-4302-b4b7-0ca50f366795	019d3867-8844-734b-90a1-dc1d25affd5c	173a617f-c955-42f6-9042-9815a0553ae6	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 16:34:28	t	2026-03-29 16:34:28	2026-03-29 16:34:28
82c277ee-b702-46f1-bbfd-036d85dce5c7	019d387b-4032-73ae-a0a4-db68da827c4d	173a617f-c955-42f6-9042-9815a0553ae6	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 16:34:28	t	2026-03-29 16:34:28	2026-03-29 16:34:28
bed23def-1dc9-4148-a903-7a785587b76a	019d3885-1b56-701e-b2f3-7ca25296665c	07bf3e5b-7d1e-409b-8e32-9df905b7b57b	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 16:34:37	t	2026-03-29 16:34:37	2026-03-29 16:34:37
e168bdd9-b417-4294-a3e9-a5d23a68a58d	019d3837-daba-7266-8af4-3cedf35e27da	07bf3e5b-7d1e-409b-8e32-9df905b7b57b	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 16:34:37	t	2026-03-29 16:34:37	2026-03-29 16:34:37
63da4088-a405-43d1-9b11-02ded8ee640b	019d386f-efdf-720e-badc-4570a7ca3df8	07bf3e5b-7d1e-409b-8e32-9df905b7b57b	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 16:34:37	t	2026-03-29 16:34:37	2026-03-29 16:34:37
ca225ae5-e9b9-4fdd-bc61-2ee0d6185803	019d3837-daba-7266-8af4-3cedf35e27da	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 16:34:44	t	2026-03-29 16:34:44	2026-03-29 16:34:44
b9131f89-dd7e-49de-ad75-9fb3ee3ba0f5	019d3867-8844-734b-90a1-dc1d25affd5c	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 16:34:44	t	2026-03-29 16:34:44	2026-03-29 16:34:44
50b2df53-8594-4130-9726-9e7cbbe97cd9	019d387e-1110-73d8-9259-156362afc755	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	ed81bd39-9041-43b8-a504-bf743b5c2919	2026-03-29 16:34:44	t	2026-03-29 16:34:44	2026-03-29 16:34:44
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, full_name, email, password, google_id, status_active, created_at, updated_at, deleted_at, avatar, email_verified_at, onboarding_step, has_seen_onboarding, onboarding_type, system_role_id) FROM stdin;
08b53454-fd00-4bbb-88f7-aa64ba26fd55	Admin Sistem Koladi	admin@koladi.com	$2y$12$zKH6i.9qBxcmAtK2LitkoO94SLGss4xiOLCfN0eehDqQSYwmXaTQ2	\N	t	2025-12-28 13:16:13	2025-12-28 13:16:13	\N	\N	2025-12-28 13:16:13	\N	f	\N	33333333-3333-3333-3333-333333333333
c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	Kuliah	kuliahbisa2005@gmail.com	$2y$12$ViCEOMGDGYz7Za9k/or.r.cNg46Ubvgjz43UQVW1/qAW9Im5B/DRe	117185357584893816951	t	2025-12-28 13:16:19	2025-12-28 13:16:44	\N	\N	\N	workspace-created	f	full	\N
019d3805-7a8b-73fe-b98e-b4876b3bf607	Riski Sapriadi	naufal201080@gmail.com	$2y$12$BVHu9nAqkNmpGrZXcuBhSe.iKdmRXk6OkcnSkFYOhb9Wc7OJ7oU6e	\N	t	2026-03-29 12:16:16	2026-03-29 12:20:15	\N	\N	\N	\N	t	member	\N
019d3881-49e4-7302-95ac-2130ca65836f	Suki	8lka2omkeo@wnbaldwy.com	$2y$12$G9q.sUK8qUzOKjQ3QUPOEOQoSRRs5SiATcX9iDjEFlS5/nnJNfrge	\N	t	2026-03-29 14:31:30	2026-03-29 14:32:00	\N	\N	\N	workspace-created	t	full	\N
019d380f-7435-7156-b5f5-3a997c9f8879	My Best Ramadhan	mybest.ramadhan@gmail.com	$2y$12$BidlxcEC7gT.2o/whJ14EeO6XQuHmKpG513gmlBDLkirnBg6jMpNe	102582539324277589038	t	2026-03-29 12:27:10	2026-03-29 12:27:28	\N	\N	\N	\N	t	full	\N
019d3885-1b56-701e-b2f3-7ca25296665c	Sukri	q0qy6ex4on@bwmyga.com	$2y$12$f3KrVwzRpH6ADOpigy0y3uGl7ljYZE4sLENW9tF8tXhOmQSZweaGa	\N	t	2026-03-29 14:35:40	2026-03-29 14:35:50	\N	\N	\N	\N	t	member	\N
019d383b-4aed-72d9-82bd-596bbdd8e4a2	Dzakwan Naufal	wixaya4731@jsncos.com	$2y$12$Kbcpz2/X4QOlQ68fjbAP1eWjqx7p05C36z6i4Ol80IEkQ5vS6gLWC	\N	t	2026-03-29 13:15:03	2026-03-29 13:15:03	\N	\N	\N	\N	f	\N	\N
019d383e-3904-7272-9831-68d831e8c9be	Mad Theo	medeso9204@smkanba.com	$2y$12$zn5ccd6PaADrlfVGJ4uHLenBPIdO6FFGq1qQAcJ21y/Sal7awW60a	\N	t	2026-03-29 13:18:15	2026-03-29 13:18:15	\N	\N	\N	\N	f	\N	\N
019d3837-daba-7266-8af4-3cedf35e27da	Riski Sapriadi	kukvu39fzu@lnovic.com	$2y$12$GiKi.we4dfzHYwVD8inksuklTIZSuF5Qrdtt0nA8Ggol5wBKAjfhm	\N	t	2026-03-29 13:11:17	2026-03-29 13:25:12	\N	\N	\N	\N	t	member	\N
019d3867-8844-734b-90a1-dc1d25affd5c	Mad Theo	lefoxik200@smkanba.com	$2y$12$GedIi.Pt/Qatw3V768oReOXwfsVQsMVB5JHym.2H/gUcfliZopB7q	\N	t	2026-03-29 14:03:22	2026-03-29 14:05:56	\N	\N	\N	\N	t	member	\N
019d386c-ddc5-72bb-b436-c4325d5ac83f	Jordan	hexaxel671@izkat.com	$2y$12$5eXuTDDB1gV2EMZ.gv2bWugmAV4TdD251fu8.i3H31lrlf6NQcNJ2	\N	t	2026-03-29 14:09:12	2026-03-29 14:09:12	\N	\N	\N	\N	f	\N	\N
019d3886-3978-7268-98fb-fe96149f4393	Our Forever Story	ourlove.2apr2025@gmail.com	$2y$12$22OKHYbveqzdRJrtjXAEc.wMa28.7gMwpARaGyEwHQozYfwpdNp.i	114817690641164151371	t	2026-03-29 14:36:53	2026-03-29 14:37:08	\N	\N	\N	\N	t	full	\N
019d386f-efdf-720e-badc-4570a7ca3df8	Jordan	cedaf73762@muncloud.com	$2y$12$3kXl5PwbQylbY0Ab31Ptku1D9hWsqvrx3xhOT5Bp6fKn3IVUx.PgG	\N	t	2026-03-29 14:12:33	2026-03-29 14:12:42	\N	\N	\N	\N	t	member	\N
019d387b-4032-73ae-a0a4-db68da827c4d	Wahyu	krii5v4jvq@ozsaip.com	$2y$12$DbPG.C6CeZpdbG5cKUVI5.uOdE3qXAuH7DK6J0VuLu.qRqdcIRyxa	\N	t	2026-03-29 14:24:54	2026-03-29 14:25:20	\N	\N	\N	\N	t	member	\N
019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	Patrick Bateman	ardhaniishere@gmail.com	$2y$12$AzWVkKuoMQnabBiUNLaWwOLu1ya/RH.2CnLxxp6flhebEn/DAzTwm	107669652105985877580	t	2026-03-08 02:17:44	2026-03-29 14:37:58	\N	\N	\N	\N	t	member	\N
019d387e-1110-73d8-9259-156362afc755	Abdullah	9uln0hwmrm@bwmyga.com	$2y$12$AJvPInDEODeE2Fs.u/Y7F.btONWA9pZV1bbLKdiR63UqKaQEe0vqq	\N	t	2026-03-29 14:27:59	2026-03-29 14:28:10	\N	\N	\N	\N	t	member	\N
019d47ce-c77e-70d0-847c-da03702ad488	Pisi Syika	syikapisi@gmail.com	$2y$12$z/bbQ4bWOIgakpiLfuluNOmLMAM/2rV0JPlWJ1zw1yFgEAIrMfNNy	116717680408890031030	t	2026-04-01 13:50:27	2026-04-01 13:51:07	\N	\N	\N	\N	t	full	\N
019ce093-8522-725c-8f9c-9b3928ec6ad3	Naufal Ardhani	naufalardhanijapan@gmail.com	$2y$12$oLSQqrIqG0d.ShssZTDhCOjBEVlAO0zvde3wJ0eW4ZJ/wFplreJ9K	103578037043880658374	t	2026-03-12 12:44:50	2026-04-04 16:51:31	\N	avatars/xgvOEsh3efRsckj5ClsJUfMvPKzBZNz1pbUxTuw4.jpg	\N	\N	t	member	\N
\.


--
-- Data for Name: workspace_performance_snapshots; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.workspace_performance_snapshots (id, workspace_id, period_start, period_end, period_type, metrics, performance_score, quality_score, risk_score, suggestions, created_at, updated_at, version) FROM stdin;
019ccce2-8389-7117-aebf-23dc9153882e	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	2026-03-02	2026-03-08	week	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 0, "totalTasks": 0, "avgProgress": 0, "medianDelay": 0, "overdueRate": 0, "maxLoadRatio": 0, "qualityScore": 0, "taskVelocity": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "empty", "urgentTaskRatio": 0, "performanceScore": 0, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "lateCompletionRate": 0}	0	0	0	{"actions": ["Buat tugas pertama untuk memulai workspace ini"], "warning": [], "critical": [], "positive": [], "empty_state": true}	2026-03-08 16:58:42	2026-03-08 16:58:42	2.0
019ce0d4-a7a2-7054-b9c1-6e4806828bb0	70ea9ff4-aa59-4cf5-b8cf-3376a201918b	2026-03-09	2026-03-15	week	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 0, "totalTasks": 0, "avgProgress": 0, "medianDelay": 0, "overdueRate": 0, "maxLoadRatio": 0, "qualityScore": 0, "taskVelocity": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "empty", "urgentTaskRatio": 0, "performanceScore": 0, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "lateCompletionRate": 0}	0	0	0	{"actions": ["Buat tugas pertama untuk memulai workspace ini"], "warning": [], "critical": [], "positive": [], "empty_state": true}	2026-03-12 13:55:58	2026-03-12 15:14:23	2.0
019cf23a-7634-7010-9484-822b3df448c7	325797d8-e3ad-4e66-a280-a8098d195bc8	2026-03-09	2026-03-15	week	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 0, "totalTasks": 0, "avgProgress": 0, "medianDelay": 0, "overdueRate": 0, "maxLoadRatio": 0, "qualityScore": 0, "taskVelocity": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "empty", "urgentTaskRatio": 0, "performanceScore": 0, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "lateCompletionRate": 0}	0	0	0	{"actions": ["Buat tugas pertama untuk memulai workspace ini"], "warning": [], "critical": [], "positive": [], "empty_state": true}	2026-03-15 23:00:43	2026-03-15 23:00:43	2.0
019d3902-4093-7164-aecb-4b79281ab206	173a617f-c955-42f6-9042-9815a0553ae6	2026-03-23	2026-03-29	week	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": -0.005393518518518519, "riskScore": 0, "onTimeRate": 0, "totalTasks": 1, "avgProgress": 0, "medianDelay": 0, "memberCount": 1, "overdueRate": 0, "maxLoadRatio": 1, "overdueCount": 0, "qualityScore": 50, "taskVelocity": -0.2, "avgDelayCapped": 0, "completionRate": 100, "tasksPerMember": 1, "workspacePhase": "active", "urgentTaskRatio": 0, "performanceScore": 25, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "hasCompletedTasks": true, "lateCompletionRate": 100}	25	50	0	{"actions": ["Review tugas yang terlambat dan cari solusi"], "warning": [], "critical": [{"title": "Mayoritas tugas selesai terlambat", "value": "0%", "metric": "onTimeRate", "actions": ["Evaluasi estimasi waktu, mungkin terlalu optimis", "Tingkatkan komunikasi untuk deteksi hambatan lebih awal"], "priority": 2, "description": "Hanya 0% tugas selesai tepat waktu"}], "positive": [], "empty_state": false}	2026-03-29 16:52:22	2026-03-29 16:58:42	2.0
019d38a7-8d19-7311-803b-32bfbacfc579	3b61e2cf-dd4b-4732-ae89-8042450187b2	2026-03-23	2026-03-29	week	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 0, "totalTasks": 0, "avgProgress": 0, "medianDelay": 0, "overdueRate": 0, "maxLoadRatio": 0, "qualityScore": 0, "taskVelocity": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "empty", "urgentTaskRatio": 0, "performanceScore": 0, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "lateCompletionRate": 0}	0	0	0	{"actions": ["Buat tugas pertama untuk memulai workspace ini"], "warning": [], "critical": [], "positive": [], "empty_state": true}	2026-03-29 15:13:17	2026-03-29 16:52:54	2.0
019d3905-a687-7015-aad0-a268b69407cd	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	2026-03-23	2026-03-29	week	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 0, "totalTasks": 0, "avgProgress": 0, "medianDelay": 0, "overdueRate": 0, "maxLoadRatio": 0, "qualityScore": 0, "taskVelocity": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "empty", "urgentTaskRatio": 0, "performanceScore": 0, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "lateCompletionRate": 0}	0	0	0	{"actions": ["Buat tugas pertama untuk memulai workspace ini"], "warning": [], "critical": [], "positive": [], "empty_state": true}	2026-03-29 16:56:04	2026-03-29 16:59:12	2.0
019d3902-5394-7060-ab3f-d85769244a79	173a617f-c955-42f6-9042-9815a0553ae6	2026-03-01	2026-03-31	month	{"gini": 0.36, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 100, "totalTasks": 7, "avgProgress": 0, "medianDelay": 0, "memberCount": 2, "overdueRate": 0, "maxLoadRatio": 6, "overdueCount": 0, "qualityScore": 100, "taskVelocity": -1008.44, "avgDelayCapped": 0, "completionRate": 100, "tasksPerMember": 3.5, "workspacePhase": "active", "urgentTaskRatio": 0, "performanceScore": 65, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "hasCompletedTasks": true, "lateCompletionRate": 0}	65	100	0	{"actions": ["Pertahankan momentum kerja", "Optimalkan proses yang sudah berjalan", "Redistribusi tugas agar lebih seimbang", "Bantu anggota yang kelebihan beban"], "warning": [{"title": "Performa perlu ditingkatkan", "value": "65/100", "metric": "performanceScore", "description": "Skor performa workspace: 65/100", "suggestions": ["Pertahankan momentum kerja", "Optimalkan proses yang sudah berjalan", "Tingkatkan komunikasi tim"]}, {"title": "Beban kerja tidak merata", "value": 0.36, "metric": "gini", "description": "Ada anggota dengan tugas terlalu banyak, ada yang terlalu sedikit", "suggestions": ["Redistribusi tugas agar lebih seimbang", "Bantu anggota yang kelebihan beban", "Review kapasitas masing-masing anggota"]}], "critical": [], "positive": [{"title": "Mayoritas tugas selesai tepat waktu", "value": "100%", "metric": "onTimeRate", "description": "100% tugas selesai on-time"}], "empty_state": false}	2026-03-29 16:52:27	2026-03-29 16:56:59	2.0
019d38fc-fa8d-72c0-bb84-8e696fc91203	3b61e2cf-dd4b-4732-ae89-8042450187b2	2026-03-01	2026-03-31	month	{"gini": 0.15, "wipRate": 25, "avgDelay": 0, "idleRate": 37.5, "maxDelay": 0, "riskScore": 60, "onTimeRate": 25, "totalTasks": 8, "avgProgress": 0, "medianDelay": 0, "memberCount": 5, "overdueRate": 25, "maxLoadRatio": 2, "overdueCount": 2, "qualityScore": 35, "taskVelocity": -0.4, "avgDelayCapped": 0, "completionRate": 25, "tasksPerMember": 1.6, "workspacePhase": "active", "urgentTaskRatio": 33.3, "performanceScore": 13, "avgTimeToDeadline": 3.7, "criticalTaskRatio": 33.3, "deadlineAdherence": 0, "hasCompletedTasks": true, "lateCompletionRate": 0}	13	35	60	{"actions": ["Prioritaskan tugas dengan deadline terdekat", "Review tugas yang terlambat dan cari solusi"], "warning": [], "critical": [{"title": "Banyak deadline mendesak", "value": "33.3%", "metric": "urgentTaskRatio", "actions": ["PRIORITY: Selesaikan tugas overdue terlebih dahulu", "Daily check-in untuk monitor blocker", "Escalate jika ada dependency issue"], "priority": 1, "description": "2 tugas sudah terlambat — Ada masalah eksekusi yang perlu segera diatasi"}, {"title": "Mayoritas tugas selesai terlambat", "value": "25%", "metric": "onTimeRate", "actions": ["Evaluasi estimasi waktu, mungkin terlalu optimis", "Tingkatkan komunikasi untuk deteksi hambatan lebih awal"], "priority": 2, "description": "Hanya 25% tugas selesai tepat waktu"}], "positive": [], "empty_state": false}	2026-03-29 16:46:36	2026-04-03 15:43:07	2.0
019d3902-ed3b-7020-b520-2938232838cf	8e26fb16-12bc-4768-b8b6-72f7d28efcc8	2026-03-01	2026-03-31	month	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 100, "totalTasks": 3, "avgProgress": 0, "medianDelay": 0, "memberCount": 3, "overdueRate": 0, "maxLoadRatio": 1, "overdueCount": 0, "qualityScore": 100, "taskVelocity": -1907.75, "avgDelayCapped": 0, "completionRate": 100, "tasksPerMember": 1, "workspacePhase": "active", "urgentTaskRatio": 0, "performanceScore": 65, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "hasCompletedTasks": true, "lateCompletionRate": 0}	65	100	0	{"actions": ["Pertahankan momentum kerja", "Optimalkan proses yang sudah berjalan"], "warning": [{"title": "Performa perlu ditingkatkan", "value": "65/100", "metric": "performanceScore", "description": "Skor performa workspace: 65/100", "suggestions": ["Pertahankan momentum kerja", "Optimalkan proses yang sudah berjalan", "Tingkatkan komunikasi tim"]}], "critical": [], "positive": [{"title": "Pembagian tugas merata", "value": 0, "metric": "gini", "description": "Beban kerja terdistribusi dengan baik"}, {"title": "Mayoritas tugas selesai tepat waktu", "value": "100%", "metric": "onTimeRate", "description": "100% tugas selesai on-time"}], "empty_state": false}	2026-03-29 16:53:06	2026-03-29 16:56:15	2.0
019d5280-bfa7-72c4-82c1-0b7efc195d10	3b61e2cf-dd4b-4732-ae89-8042450187b2	2026-03-30	2026-04-05	week	{"gini": 0.15, "wipRate": 25, "avgDelay": 0, "idleRate": 37.5, "maxDelay": 0, "riskScore": 60, "onTimeRate": 25, "totalTasks": 8, "avgProgress": 0, "medianDelay": 0, "memberCount": 5, "overdueRate": 25, "maxLoadRatio": 2, "overdueCount": 2, "qualityScore": 35, "taskVelocity": -0.4, "avgDelayCapped": 0, "completionRate": 25, "tasksPerMember": 1.6, "workspacePhase": "active", "urgentTaskRatio": 33.3, "performanceScore": 13, "avgTimeToDeadline": 3.7, "criticalTaskRatio": 33.3, "deadlineAdherence": 0, "hasCompletedTasks": true, "lateCompletionRate": 0}	13	35	60	{"actions": ["Prioritaskan tugas dengan deadline terdekat", "Review tugas yang terlambat dan cari solusi"], "warning": [], "critical": [{"title": "Banyak deadline mendesak", "value": "33.3%", "metric": "urgentTaskRatio", "actions": ["PRIORITY: Selesaikan tugas overdue terlebih dahulu", "Daily check-in untuk monitor blocker", "Escalate jika ada dependency issue"], "priority": 1, "description": "2 tugas sudah terlambat — Ada masalah eksekusi yang perlu segera diatasi"}, {"title": "Mayoritas tugas selesai terlambat", "value": "25%", "metric": "onTimeRate", "actions": ["Evaluasi estimasi waktu, mungkin terlalu optimis", "Tingkatkan komunikasi untuk deteksi hambatan lebih awal"], "priority": 2, "description": "Hanya 25% tugas selesai tepat waktu"}], "positive": [], "empty_state": false}	2026-04-03 15:41:02	2026-04-03 15:41:02	2.0
019d5282-b698-738b-9e38-33d9ce416583	3b61e2cf-dd4b-4732-ae89-8042450187b2	2026-02-01	2026-02-28	week	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 0, "totalTasks": 0, "avgProgress": 0, "medianDelay": 0, "overdueRate": 0, "maxLoadRatio": 0, "qualityScore": 0, "taskVelocity": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "empty", "urgentTaskRatio": 0, "performanceScore": 0, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "lateCompletionRate": 0}	0	0	0	{"actions": ["Buat tugas pertama untuk memulai workspace ini"], "warning": [], "critical": [], "positive": [], "empty_state": true}	2026-04-03 15:43:11	2026-04-03 15:43:11	2.0
019d5282-f8bd-73d7-8984-24123e8864ba	3b61e2cf-dd4b-4732-ae89-8042450187b2	2025-12-01	2025-12-31	month	{"gini": 0, "wipRate": 0, "avgDelay": 0, "idleRate": 0, "maxDelay": 0, "riskScore": 0, "onTimeRate": 0, "totalTasks": 0, "avgProgress": 0, "medianDelay": 0, "overdueRate": 0, "maxLoadRatio": 0, "qualityScore": 0, "taskVelocity": 0, "completionRate": 0, "tasksPerMember": 0, "workspacePhase": "empty", "urgentTaskRatio": 0, "performanceScore": 0, "avgTimeToDeadline": 0, "criticalTaskRatio": 0, "deadlineAdherence": 0, "lateCompletionRate": 0}	0	0	0	{"actions": ["Buat tugas pertama untuk memulai workspace ini"], "warning": [], "critical": [], "positive": [], "empty_state": true}	2026-04-03 15:43:28	2026-04-03 15:43:28	2.0
\.


--
-- Data for Name: workspaces; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.workspaces (id, company_id, type, name, created_by, created_at, updated_at, deleted_at, description) FROM stdin;
f7b296c0-c0b4-496b-a55e-b99e3692e1cf	a55a03f3-2191-4b53-833c-d7de8ce62c9b	Tim	aaa	c2b542eb-0f2b-4c18-9f4b-5ef30a5e3c86	2025-12-28 13:16:52	2025-12-28 13:16:52	\N	aaa
70ea9ff4-aa59-4cf5-b8cf-3376a201918b	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	Tim	testing	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 10:30:24	2026-03-08 10:30:24	\N	\N
826f878b-ea65-43cd-923d-531b3ddf4599	6f47e463-8fe1-4f3f-b1bb-c5b42fd63433	Proyek	huhuhu	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-08 17:00:52	2026-03-08 17:00:52	\N	\N
325797d8-e3ad-4e66-a280-a8098d195bc8	da242fb4-a281-4339-a8a4-9a66436e237d	Tim	ss	019ce093-8522-725c-8f9c-9b3928ec6ad3	2026-03-15 22:57:44	2026-03-15 22:57:44	\N	\N
3b61e2cf-dd4b-4732-ae89-8042450187b2	31c7b915-01ea-40ed-80be-723ffe01c10d	Tim	Div. Desain	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 14:58:18	2026-03-29 14:58:18	\N	\N
173a617f-c955-42f6-9042-9815a0553ae6	31c7b915-01ea-40ed-80be-723ffe01c10d	Tim	HR Keuangan	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 16:25:50	2026-03-29 16:25:50	\N	\N
8e26fb16-12bc-4768-b8b6-72f7d28efcc8	31c7b915-01ea-40ed-80be-723ffe01c10d	Proyek	MOU PT Xyz	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 16:33:56	2026-03-29 16:33:56	\N	\N
07bf3e5b-7d1e-409b-8e32-9df905b7b57b	31c7b915-01ea-40ed-80be-723ffe01c10d	Proyek	Implementasi "Habit-Loop Enterprise"	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-03-29 14:58:26	2026-03-29 16:53:21	2026-03-29 16:53:21	\N
f925311c-f164-4133-8014-2de78bdebaec	aed661f0-a039-4927-8259-6ea71f9943f7	Tim	asdf	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-04 11:07:52	2026-04-04 11:07:52	\N	\N
a9a33d65-19f4-4aed-b677-934bb14721e4	aed661f0-a039-4927-8259-6ea71f9943f7	Proyek	askfjlaksdj	019cc9bb-f6fd-728e-80b2-8baf57ac6b6e	2026-04-04 12:48:40	2026-04-04 12:48:40	\N	\N
\.


--
-- Name: feedbacks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.feedbacks_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 7, true);


--
-- Name: otp_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.otp_verifications_id_seq', 13, true);


--
-- Name: addons addons_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.addons
    ADD CONSTRAINT addons_pkey PRIMARY KEY (id);


--
-- Name: announcement_recipients announcement_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_pkey PRIMARY KEY (id);


--
-- Name: announcements announcements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_pkey PRIMARY KEY (id);


--
-- Name: attachments attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_pkey PRIMARY KEY (id);


--
-- Name: board_columns board_columns_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: calendar_events calendar_events_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_pkey PRIMARY KEY (id);


--
-- Name: calendar_participants calendar_participants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_pkey PRIMARY KEY (id);


--
-- Name: checklists checklists_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.checklists
    ADD CONSTRAINT checklists_pkey PRIMARY KEY (id);


--
-- Name: colors colors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.colors
    ADD CONSTRAINT colors_pkey PRIMARY KEY (id);


--
-- Name: comments comments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_pkey PRIMARY KEY (id);


--
-- Name: companies companies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_pkey PRIMARY KEY (id);


--
-- Name: conversation_participants conversation_participants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_pkey PRIMARY KEY (id);


--
-- Name: conversations conversations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_pkey PRIMARY KEY (id);


--
-- Name: document_recipients document_recipients_document_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.document_recipients
    ADD CONSTRAINT document_recipients_document_id_user_id_unique UNIQUE (document_id, user_id);


--
-- Name: document_recipients document_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.document_recipients
    ADD CONSTRAINT document_recipients_pkey PRIMARY KEY (id);


--
-- Name: feedbacks feedbacks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.feedbacks
    ADD CONSTRAINT feedbacks_pkey PRIMARY KEY (id);


--
-- Name: files files_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_pkey PRIMARY KEY (id);


--
-- Name: folders folders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_pkey PRIMARY KEY (id);


--
-- Name: insight_recipients insight_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_pkey PRIMARY KEY (id);


--
-- Name: insights insights_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_pkey PRIMARY KEY (id);


--
-- Name: invitations invitations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_pkey PRIMARY KEY (id);


--
-- Name: labels labels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.labels
    ADD CONSTRAINT labels_pkey PRIMARY KEY (id);


--
-- Name: leave_requests leave_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_pkey PRIMARY KEY (id);


--
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: mindmap_nodes mindmap_nodes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_pkey PRIMARY KEY (id);


--
-- Name: mindmaps mindmaps_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmaps
    ADD CONSTRAINT mindmaps_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: otp_verifications otp_verifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.otp_verifications
    ADD CONSTRAINT otp_verifications_pkey PRIMARY KEY (id);


--
-- Name: plans plans_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.plans
    ADD CONSTRAINT plans_pkey PRIMARY KEY (id);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: subscription_invoices subscription_invoices_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT subscription_invoices_pkey PRIMARY KEY (id);


--
-- Name: subscriptions subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_pkey PRIMARY KEY (id);


--
-- Name: task_assignments task_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_pkey PRIMARY KEY (id);


--
-- Name: task_labels task_labels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_pkey PRIMARY KEY (task_id, label_id);


--
-- Name: tasks tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_pkey PRIMARY KEY (id);


--
-- Name: user_companies user_companies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_pkey PRIMARY KEY (id);


--
-- Name: user_workspaces user_workspaces_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_pkey PRIMARY KEY (id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: workspace_performance_snapshots workspace_performance_snapshots_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspace_performance_snapshots
    ADD CONSTRAINT workspace_performance_snapshots_pkey PRIMARY KEY (id);


--
-- Name: workspaces workspaces_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_pkey PRIMARY KEY (id);


--
-- Name: conversations_scope_company_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_scope_company_id_index ON public.conversations USING btree (scope, company_id);


--
-- Name: conversations_scope_workspace_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversations_scope_workspace_id_index ON public.conversations USING btree (scope, workspace_id);


--
-- Name: idx_attachments_attachable; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attachments_attachable ON public.attachments USING btree (attachable_type, attachable_id);


--
-- Name: idx_attachments_uploaded_by; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attachments_uploaded_by ON public.attachments USING btree (uploaded_by);


--
-- Name: idx_board_columns_workspace_position; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_board_columns_workspace_position ON public.board_columns USING btree (workspace_id, "position");


--
-- Name: idx_checklists_task; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_checklists_task ON public.checklists USING btree (task_id);


--
-- Name: idx_conversation_participants_conversation_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversation_participants_conversation_id ON public.conversation_participants USING btree (conversation_id);


--
-- Name: idx_conversation_participants_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversation_participants_user_id ON public.conversation_participants USING btree (user_id);


--
-- Name: idx_conversations_last_message_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_conversations_last_message_id ON public.conversations USING btree (last_message_id);


--
-- Name: idx_files_company_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_files_company_id ON public.files USING btree (company_id);


--
-- Name: idx_folders_company_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_folders_company_id ON public.folders USING btree (company_id);


--
-- Name: idx_messages_conversation_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_messages_conversation_id ON public.messages USING btree (conversation_id);


--
-- Name: idx_mindmap_nodes_mindmap_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_mindmap_nodes_mindmap_id ON public.mindmap_nodes USING btree (mindmap_id);


--
-- Name: idx_mindmap_nodes_parent_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_mindmap_nodes_parent_id ON public.mindmap_nodes USING btree (parent_id);


--
-- Name: idx_subscription_invoices_payment_method; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_subscription_invoices_payment_method ON public.subscription_invoices USING btree (payment_method);


--
-- Name: idx_subscription_invoices_verified_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_subscription_invoices_verified_at ON public.subscription_invoices USING btree (verified_at);


--
-- Name: idx_task_assignments_task_user; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_assignments_task_user ON public.task_assignments USING btree (task_id, user_id);


--
-- Name: idx_task_assignments_user; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_assignments_user ON public.task_assignments USING btree (user_id);


--
-- Name: idx_task_labels_label; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_labels_label ON public.task_labels USING btree (label_id);


--
-- Name: idx_task_labels_task; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_task_labels_task ON public.task_labels USING btree (task_id);


--
-- Name: idx_tasks_due_datetime; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_due_datetime ON public.tasks USING btree (due_datetime) WHERE (deleted_at IS NULL);


--
-- Name: idx_tasks_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_status ON public.tasks USING btree (status) WHERE (deleted_at IS NULL);


--
-- Name: idx_tasks_workspace_column; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_column ON public.tasks USING btree (workspace_id, board_column_id);


--
-- Name: idx_tasks_workspace_creator; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_creator ON public.tasks USING btree (workspace_id, created_by);


--
-- Name: idx_tasks_workspace_secret; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_tasks_workspace_secret ON public.tasks USING btree (workspace_id, is_secret);


--
-- Name: notifications_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_created_at_index ON public.notifications USING btree (created_at);


--
-- Name: notifications_type_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_type_user_id_index ON public.notifications USING btree (type, user_id);


--
-- Name: notifications_user_id_company_id_is_read_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_user_id_company_id_is_read_index ON public.notifications USING btree (user_id, company_id, is_read);


--
-- Name: otp_verifications_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX otp_verifications_email_index ON public.otp_verifications USING btree (email);


--
-- Name: subscription_invoices_external_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_external_id_index ON public.subscription_invoices USING btree (external_id);


--
-- Name: subscription_invoices_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_status_index ON public.subscription_invoices USING btree (status);


--
-- Name: subscription_invoices_subscription_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscription_invoices_subscription_id_index ON public.subscription_invoices USING btree (subscription_id);


--
-- Name: subscriptions_company_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscriptions_company_id_index ON public.subscriptions USING btree (company_id);


--
-- Name: subscriptions_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subscriptions_status_index ON public.subscriptions USING btree (status);


--
-- Name: ws_perf_idx_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ws_perf_idx_created_at ON public.workspace_performance_snapshots USING btree (created_at);


--
-- Name: invitations update_invitations_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_invitations_updated_at BEFORE UPDATE ON public.invitations FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: announcement_recipients announcement_recipients_announcement_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_announcement_id_fkey FOREIGN KEY (announcement_id) REFERENCES public.announcements(id) ON DELETE CASCADE;


--
-- Name: announcement_recipients announcement_recipients_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcement_recipients
    ADD CONSTRAINT announcement_recipients_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: announcements announcements_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE SET NULL;


--
-- Name: announcements announcements_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: announcements announcements_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: attachments attachments_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(id);


--
-- Name: board_columns board_columns_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: board_columns board_columns_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.board_columns
    ADD CONSTRAINT board_columns_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: calendar_events calendar_events_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: calendar_events calendar_events_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT calendar_events_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: calendar_participants calendar_participants_event_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_event_id_fkey FOREIGN KEY (event_id) REFERENCES public.calendar_events(id) ON DELETE CASCADE;


--
-- Name: calendar_participants calendar_participants_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_participants
    ADD CONSTRAINT calendar_participants_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: checklists checklists_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.checklists
    ADD CONSTRAINT checklists_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- Name: comments comments_parent_comment_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_parent_comment_id_fkey FOREIGN KEY (parent_comment_id) REFERENCES public.comments(id) ON DELETE CASCADE;


--
-- Name: comments comments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: conversation_participants conversation_participants_conversation_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_conversation_id_fkey FOREIGN KEY (conversation_id) REFERENCES public.conversations(id) ON DELETE CASCADE;


--
-- Name: conversation_participants conversation_participants_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversation_participants
    ADD CONSTRAINT conversation_participants_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: conversations conversations_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: conversations conversations_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: conversations conversations_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: conversations conversations_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id);


--
-- Name: files files_folder_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_folder_id_fkey FOREIGN KEY (folder_id) REFERENCES public.folders(id) ON DELETE CASCADE;


--
-- Name: files files_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(id);


--
-- Name: files files_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT files_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: calendar_events fk_calendar_events_company_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calendar_events
    ADD CONSTRAINT fk_calendar_events_company_id FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: conversations fk_conversations_last_message; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT fk_conversations_last_message FOREIGN KEY (last_message_id) REFERENCES public.messages(id) ON DELETE SET NULL;


--
-- Name: files fk_files_company; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.files
    ADD CONSTRAINT fk_files_company FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: folders fk_folders_company; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT fk_folders_company FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: subscription_invoices fk_subscription_invoices_verified_by; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT fk_subscription_invoices_verified_by FOREIGN KEY (verified_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: workspace_performance_snapshots fk_wps_workspace; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspace_performance_snapshots
    ADD CONSTRAINT fk_wps_workspace FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: folders folders_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: folders folders_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.folders
    ADD CONSTRAINT folders_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: insight_recipients insight_recipients_insight_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_insight_id_fkey FOREIGN KEY (insight_id) REFERENCES public.insights(id) ON DELETE CASCADE;


--
-- Name: insight_recipients insight_recipients_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insight_recipients
    ADD CONSTRAINT insight_recipients_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: insights insights_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: insights insights_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insights
    ADD CONSTRAINT insights_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: invitations invitations_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id);


--
-- Name: invitations invitations_invited_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_invited_by_fkey FOREIGN KEY (invited_by) REFERENCES public.users(id);


--
-- Name: labels labels_color_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.labels
    ADD CONSTRAINT labels_color_id_fkey FOREIGN KEY (color_id) REFERENCES public.colors(id);


--
-- Name: labels labels_workspace_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.labels
    ADD CONSTRAINT labels_workspace_id_foreign FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: leave_requests leave_requests_approved_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_approved_by_fkey FOREIGN KEY (approved_by) REFERENCES public.users(id);


--
-- Name: leave_requests leave_requests_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: leave_requests leave_requests_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leave_requests
    ADD CONSTRAINT leave_requests_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: messages messages_conversation_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_conversation_id_fkey FOREIGN KEY (conversation_id) REFERENCES public.conversations(id) ON DELETE CASCADE;


--
-- Name: messages messages_reply_to_message_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_reply_to_message_id_fkey FOREIGN KEY (reply_to_message_id) REFERENCES public.messages(id);


--
-- Name: messages messages_sender_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_sender_id_fkey FOREIGN KEY (sender_id) REFERENCES public.users(id);


--
-- Name: mindmap_nodes mindmap_nodes_mindmap_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_mindmap_id_fkey FOREIGN KEY (mindmap_id) REFERENCES public.mindmaps(id) ON DELETE CASCADE;


--
-- Name: mindmap_nodes mindmap_nodes_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmap_nodes
    ADD CONSTRAINT mindmap_nodes_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES public.mindmap_nodes(id) ON DELETE CASCADE;


--
-- Name: mindmaps mindmaps_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mindmaps
    ADD CONSTRAINT mindmaps_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: notifications notifications_actor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_actor_id_foreign FOREIGN KEY (actor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: notifications notifications_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: notifications notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: notifications notifications_workspace_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_workspace_id_foreign FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: sessions sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: subscription_invoices subscription_invoices_subscription_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscription_invoices
    ADD CONSTRAINT subscription_invoices_subscription_id_foreign FOREIGN KEY (subscription_id) REFERENCES public.subscriptions(id) ON DELETE CASCADE;


--
-- Name: subscriptions subscriptions_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: subscriptions subscriptions_plan_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_plan_id_foreign FOREIGN KEY (plan_id) REFERENCES public.plans(id) ON DELETE SET NULL;


--
-- Name: task_assignments task_assignments_task_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_task_id_fkey FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- Name: task_assignments task_assignments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_assignments
    ADD CONSTRAINT task_assignments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: task_labels task_labels_label_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_label_id_foreign FOREIGN KEY (label_id) REFERENCES public.labels(id) ON DELETE CASCADE;


--
-- Name: task_labels task_labels_task_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.task_labels
    ADD CONSTRAINT task_labels_task_id_foreign FOREIGN KEY (task_id) REFERENCES public.tasks(id) ON DELETE CASCADE;


--
-- Name: tasks tasks_board_column_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_board_column_id_fkey FOREIGN KEY (board_column_id) REFERENCES public.board_columns(id);


--
-- Name: tasks tasks_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: tasks tasks_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tasks
    ADD CONSTRAINT tasks_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: user_companies user_companies_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: user_companies user_companies_roles_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_roles_id_fkey FOREIGN KEY (roles_id) REFERENCES public.roles(id);


--
-- Name: user_companies user_companies_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_companies
    ADD CONSTRAINT user_companies_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: user_workspaces user_workspaces_roles_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_roles_id_fkey FOREIGN KEY (roles_id) REFERENCES public.roles(id);


--
-- Name: user_workspaces user_workspaces_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: user_workspaces user_workspaces_workspace_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_workspaces
    ADD CONSTRAINT user_workspaces_workspace_id_fkey FOREIGN KEY (workspace_id) REFERENCES public.workspaces(id) ON DELETE CASCADE;


--
-- Name: users users_system_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_system_role_id_foreign FOREIGN KEY (system_role_id) REFERENCES public.roles(id) ON DELETE SET NULL;


--
-- Name: workspaces workspaces_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_company_id_fkey FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE CASCADE;


--
-- Name: workspaces workspaces_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.workspaces
    ADD CONSTRAINT workspaces_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict fJDXhXHGsiotnyWxaP4rR4ZBzRilXUSAwWMce3dpkYz8bxlWXYa6IeLJgKH7By3

